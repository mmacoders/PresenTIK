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
        $rankingData = $users->map(function ($user) use ($startDate, $endDate, $jamMasuk, $gracePeriod, $denominator) {
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
            
            // Raw Metrics
            $avgLateMinutes = $presentCount > 0 ? ($totalLateMinutes / $presentCount) : 0;

            // --- SCORING Logic (Absolute Thresholds) ---
            
            // C1: Kehadiran (30%)
            // Formula: (Hadir / Total Hari Kerja) * 100
            $valC1 = ($presentCount / $denominator) * 100;
            $scoreC1 = max(0, min(100, $valC1)); // Clamp 0-100

            // C2: Ketepatan Waktu (20%) - Avg Late Minutes
            // * 0 menit -> 100
            // * 1-5 menit -> 80
            // * 6-10 menit -> 60
            // * > 10 menit -> 40
            if ($avgLateMinutes <= 0) $scoreC2 = 100;
            elseif ($avgLateMinutes <= 5) $scoreC2 = 80;
            elseif ($avgLateMinutes <= 10) $scoreC2 = 60;
            else $scoreC2 = 40;
            
            // C3: Bebas Telat (20%) - Frequency
            // * 0 kali -> 100
            // * 1-2 kali -> 70
            // * 3-5 kali -> 40
            // * > 5 kali -> 0
            if ($lateCount == 0) $scoreC3 = 100;
            elseif ($lateCount <= 2) $scoreC3 = 70;
            elseif ($lateCount <= 5) $scoreC3 = 40;
            else $scoreC3 = 0;
            
            // C4: Bebas Alpha (20%)
            // * 0 hari -> 100
            // * 1 hari -> 60
            // * 2 hari -> 40
            // * >=3 hari -> 0
            if ($alphaCount == 0) $scoreC4 = 100;
            elseif ($alphaCount == 1) $scoreC4 = 60;
            elseif ($alphaCount == 2) $scoreC4 = 40;
            else $scoreC4 = 0;

            // C5: Konsistensi (10%)
            // Formula: Hadir / (Total Hari - Izin) * 100
            $valC5 = ($effectiveWorkingDays > 0) ? (($presentCount / $effectiveWorkingDays) * 100) : 0;
            $scoreC5 = max(0, min(100, $valC5));

            // Weights
            $w1 = 0.30;
            $w2 = 0.20;
            $w3 = 0.20;
            $w4 = 0.20;
            $w5 = 0.10;

            // Final Calculation
            $finalScore = (
                ($scoreC1 * $w1) +
                ($scoreC2 * $w2) +
                ($scoreC3 * $w3) +
                ($scoreC4 * $w4) +
                ($scoreC5 * $w5)
            );
            
            // Category Classification
            /*
            | 90 – 100 | Sangat Disiplin |
            | 75 – 89  | Disiplin        |
            | 60 – 74  | Cukup           |
            | 40 – 59  | Kurang          |
            | < 40     | Tidak Disiplin  |
            */
            $category = 'Tidak Disiplin';
            if ($finalScore >= 90) {
                $category = 'Sangat Disiplin';
            } elseif ($finalScore >= 75) {
                $category = 'Disiplin';
            } elseif ($finalScore >= 60) {
                $category = 'Cukup';
            } elseif ($finalScore >= 40) {
                $category = 'Kurang';
            }
            
            return [
                'user' => $user->only(['id', 'name', 'nip', 'jabatan']),
                'metrics' => [
                    'working_days' => $denominator,
                    'present' => $presentCount,
                    'izin' => $izinDays,
                    'alpha' => $alphaCount,
                    'late_count' => $lateCount,
                    'avg_late_min' => round($avgLateMinutes, 1),
                ],
                // Return threshold-based scores (already normalized 0-100)
                'scores' => [
                    'attendance' => round($scoreC1, 1),
                    'punctuality' => round($scoreC2, 1),
                    'late_freq' => round($scoreC3, 1),
                    'alpha' => round($scoreC4, 1),
                    'consistency' => round($scoreC5, 1),
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
