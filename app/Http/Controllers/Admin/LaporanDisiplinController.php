<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class LaporanDisiplinController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // Settings
        $settings = SystemSetting::first();
        $jamMasuk = $settings ? Carbon::parse($settings->jam_masuk) : Carbon::parse('08:00:00');
        $gracePeriod = $settings ? $settings->grace_period_minutes : 0;
        
        // Users
        $users = User::where('role', 'user')->orderBy('name')->get();
        
        // Working Days (Exclude Weekends)
        $period = CarbonPeriod::create($startDate, $endDate);
        $workingDaysCount = 0;
        foreach ($period as $date) {
            if (!$date->isWeekend()) {
                $workingDaysCount++;
            }
        }
        
        // Days to count logic (up to today or full month)
        $daysToCount = $workingDaysCount ?: 1;
        if ($endDate->isFuture()) {
            $periodUpToNow = CarbonPeriod::create($startDate, Carbon::now()->endOfDay()->min($endDate));
            $daysToCount = 0;
            foreach($periodUpToNow as $d) {
                if (!$d->isWeekend()) $daysToCount++;
            }
        }
        $denominator = $daysToCount ?: 1;

        // Step 1: Collect Raw Data for all users
        $rawData = $users->map(function ($user) use ($startDate, $endDate, $jamMasuk, $gracePeriod, $denominator) {
            // Get Absensi
            $absensis = Absensi::where('user_id', $user->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->get();
                
            // Get Izin (Approved)
            $izins = Izin::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'disetujui'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                      ->orWhereBetween('tanggal_selesai', [$startDate, $endDate]);
                })
                ->get();

            // Calculate Metrics
            $presentCount = $absensis->count();
            
            // Calculate Late
            $lateCount = 0;
            $totalLateMinutes = 0;
            
            foreach ($absensis as $absensi) {
                if ($absensi->waktu_masuk) {
                    // Parse check-in time safe
                    $checkInTime = Carbon::parse($absensi->waktu_masuk);
                    
                    // Normalize to comparison date (Today)
                    $comparisonDate = Carbon::now()->format('Y-m-d');
                    
                    $checkInNormalized = Carbon::parse($comparisonDate . ' ' . $checkInTime->format('H:i:s'));
                    $thresholdNormalized = Carbon::parse($comparisonDate . ' ' . $jamMasuk->format('H:i:s'))
                                               ->addMinutes($gracePeriod);
                    
                    if ($checkInNormalized->gt($thresholdNormalized)) {
                        $lateCount++;
                        $diff = $checkInNormalized->diffInMinutes($thresholdNormalized);
                        $totalLateMinutes += $diff;
                    }
                }
            }
            
            // Calculate Izin Days
            $izinDays = 0;
            foreach ($izins as $izin) {
                $permitStart = Carbon::parse($izin->tanggal_mulai);
                $permitEnd = Carbon::parse($izin->tanggal_selesai);
                
                if ($permitStart->lt($startDate)) $permitStart = $startDate->copy();
                if ($permitEnd->gt($endDate)) $permitEnd = $endDate->copy();
                
                $izinPeriod = CarbonPeriod::create($permitStart, $permitEnd);
                foreach ($izinPeriod as $d) {
                    if (!$d->isWeekend()) {
                        $izinDays++;
                    }
                }
            }
            
            $alphaCount = max(0, $denominator - ($presentCount + $izinDays));
            $effectiveWorkingDays = max(1, $denominator - $izinDays);
            
            // Raw Criteria Values
            return [
                'user' => $user,
                'metrics' => [
                    'working_days' => $denominator,
                    'present' => $presentCount,
                    'izin' => $izinDays,
                    'alpha' => $alphaCount,
                    'late_count' => $lateCount,
                    'avg_late_min' => $presentCount > 0 ? ($totalLateMinutes / $presentCount) : 0,
                    'effective_days' => $effectiveWorkingDays
                ],
                // Raw attributes for SAW (C1..C5)
                'c1_attendance' => $presentCount, // Benefit
                'c2_punctuality' => $presentCount > 0 ? ($totalLateMinutes / $presentCount) : 0, // Cost
                'c3_late_freq' => $lateCount,  // Cost
                'c4_alpha' => $alphaCount,     // Cost
                'c5_consistency' => ($presentCount / $effectiveWorkingDays) // Benefit (Ratio)
            ];
        });

        // Step 2: Determine Max/Min for Normalization
        $maxC1 = $rawData->max('c1_attendance') ?: 1;
        $maxC2 = $rawData->max('c2_punctuality') ?: 0;
        $maxC3 = $rawData->max('c3_late_freq') ?: 0;
        $maxC4 = $rawData->max('c4_alpha') ?: 0;
        $maxC5 = $rawData->max('c5_consistency') ?: 1;
        
        $minC2 = $rawData->min('c2_punctuality') ?: 0;
        $minC3 = $rawData->min('c3_late_freq') ?: 0;
        $minC4 = $rawData->min('c4_alpha') ?: 0;

        // SAW Weights
        $w1 = 0.30; // Kehadiran
        $w2 = 0.20; // Ketepatan Waktu
        $w3 = 0.20; // Frekuensi Terlambat
        $w4 = 0.20; // Alpha
        $w5 = 0.10; // Konsistensi

        // Step 3: Calculate SAW Scores
        $rankingData = $rawData->map(function ($item) use (
            $maxC1, $maxC2, $maxC3, $maxC4, $maxC5, 
            $minC2, $minC3, $minC4,
            $w1, $w2, $w3, $w4, $w5
        ) {
            // Normalization
            // Benefit: x / Max
            // Cost (Linear): (Max - x) / (Max - Min). If Max=Min, score is 1 (perfect).
            
            $r1 = $item['c1_attendance'] / $maxC1;
            
            $r2 = ($maxC2 == $minC2) ? 1 : (($maxC2 - $item['c2_punctuality']) / ($maxC2 - $minC2));
            $r3 = ($maxC3 == $minC3) ? 1 : (($maxC3 - $item['c3_late_freq']) / ($maxC3 - $minC3));
            $r4 = ($maxC4 == $minC4) ? 1 : (($maxC4 - $item['c4_alpha']) / ($maxC4 - $minC4));
            
            $r5 = $item['c5_consistency'] / $maxC5; // Actually this is already a ratio 0-1, but strictly SAW normalizes against the best in group.

            // Calculate Final Score (Scale to 100)
            $finalScore = (
                ($r1 * $w1) +
                ($r2 * $w2) +
                ($r3 * $w3) +
                ($r4 * $w4) +
                ($r5 * $w5)
            ) * 100;
            
            // Category
            $category = 'Kurang Disiplin';
            if ($finalScore >= 85) {
                $category = 'Sangat Disiplin';
            } elseif ($finalScore >= 70) {
                $category = 'Cukup Disiplin';
            }

            return [
                'user' => $item['user']->only(['id', 'name', 'nip', 'jabatan']),
                'metrics' => [
                    'working_days' => $item['metrics']['working_days'],
                    'present' => $item['metrics']['present'],
                    'izin' => $item['metrics']['izin'],
                    'alpha' => $item['metrics']['alpha'],
                    'late_count' => $item['metrics']['late_count'],
                    'avg_late_min' => round($item['metrics']['avg_late_min'], 1),
                ],
                'scores' => [
                    // Display normalized scores scaled to 100 for better readability in UI
                    'attendance' => round($r1 * 100, 1),
                    'punctuality' => round($r2 * 100, 1),
                    'late_freq' => round($r3 * 100, 1),
                    'alpha' => round($r4 * 100, 1),
                    'consistency' => round($r5 * 100, 1),
                ],
                'final_score' => round($finalScore, 2),
                'category' => $category,
            ];
        });
        
        // Sort by Final Score Desc
        $rankingData = $rankingData->sortByDesc('final_score')->values();

        return Inertia::render('Admin/Laporan/Disiplin', [
            'rankingData' => $rankingData,
            'filters' => [
                'month' => $month,
                'year' => $year,
            ]
        ]);
    }
}
