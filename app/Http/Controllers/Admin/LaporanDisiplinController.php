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
        
        // Prevent division by zero
        $workingDaysCount = $workingDaysCount ?: 1;

        $rankingData = $users->map(function ($user) use ($startDate, $endDate, $workingDaysCount, $jamMasuk, $gracePeriod) {
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
                    $threshold = $jamMasuk->copy()->addMinutes($gracePeriod);
                    
                    // We need to set the date of threshold to the date of absensi to compare correctly
                    // Or easier: compare time strings H:i:s
                    $absensiTime = Carbon::createFromFormat('H:i:s', $absensi->waktu_masuk);
                    $thresholdTime = Carbon::createFromFormat('H:i:s', $threshold->format('H:i:s'));
                    
                    if ($absensiTime->gt($thresholdTime)) {
                        $lateCount++;
                        $diff = $absensiTime->diffInMinutes($thresholdTime);
                        $totalLateMinutes += $diff;
                    }
                }
            }
            
            // Calculate Izin Days
            $izinDays = 0;
            foreach ($izins as $izin) {
                // Intersect izin period with report period and working days
                $permitStart = Carbon::parse($izin->tanggal_mulai);
                $permitEnd = Carbon::parse($izin->tanggal_selesai);
                
                // Clamp to month
                if ($permitStart->lt($startDate)) $permitStart = $startDate->copy();
                if ($permitEnd->gt($endDate)) $permitEnd = $endDate->copy();
                
                $izinPeriod = CarbonPeriod::create($permitStart, $permitEnd);
                foreach ($izinPeriod as $d) {
                    if (!$d->isWeekend()) {
                        $izinDays++;
                    }
                }
            }
            
            // Calculate Alpha
            // Alpha = WorkingDays - (Present + Izin)
            // Ensure non-negative and don't count future days if looking at current month
            // But usually reports cover the whole month. If strict "Alpha", we count days past.
            
            // For fairness in current month, we should probably only count up to "Today" if current month.
            $daysToCount = $workingDaysCount;
            if ($endDate->isFuture()) {
                // If reporting current month, only count working days up to today
                $periodUpToNow = CarbonPeriod::create($startDate, Carbon::now()->endOfDay()->min($endDate));
                $daysToCount = 0;
                foreach($periodUpToNow as $d) {
                    if (!$d->isWeekend()) $daysToCount++;
                }
            }
            
            // Re-calculate Alpha based on days passed
            $alphaCount = max(0, $daysToCount - ($presentCount + $izinDays));
            
            // --- SCORING LOGIC ---
            
            // 1. Attendance Percentage (30%)
            // Formula: (Present / WorkingDays) * 100
            // Use full working days for projection or days passed?
            // Let's use days passed to be fair for mid-month check.
            $denominator = $daysToCount ?: 1;
            
            $attendanceScore = ($presentCount / $denominator) * 100;
            
            // 2. Punctuality (Average Late Minutes) (20%)
            // If avg late is 0 -> 100. If 60 mins late -> 40.
            $avgLateMinutes = $presentCount > 0 ? ($totalLateMinutes / $presentCount) : 0;
            $punctualityScore = max(0, 100 - $avgLateMinutes); 
            
            // 3. Lateness Frequency (20%)
            // Factor: How many times late vs Present.
            // If 0 late -> 100.
            // If 100% late -> 0.
            $lateFreqRatio = $presentCount > 0 ? ($lateCount / $presentCount) : 0;
            $lateFreqScore = (1 - $lateFreqRatio) * 100;
            
            // 4. Alpha (20%)
            // Heavy penalty. 
            // (1 - (Alpha/DaysPassed)) * 100
            $alphaScore = (1 - ($alphaCount / $denominator)) * 100;
            $alphaScore = max(0, $alphaScore); // prevent negative
            
            // 5. Attendance Consistency (10%)
            // (Present / (DaysPassed - Izin)) * 100.
            // i.e. Reliability when expected to be there.
            $effectiveWorkingDays = max(1, $denominator - $izinDays);
            $consistencyScore = ($presentCount / $effectiveWorkingDays) * 100;
            $consistencyScore = min(100, $consistencyScore); // Cap at 100

            
            // Weighted Sum
            $finalScore = (
                ($attendanceScore * 0.30) +
                ($punctualityScore * 0.20) +
                ($lateFreqScore * 0.20) +
                ($alphaScore * 0.20) +
                ($consistencyScore * 0.10)
            );
            
            // Category
            $category = 'Kurang Disiplin';
            if ($finalScore >= 85) {
                $category = 'Sangat Disiplin';
            } elseif ($finalScore >= 70) {
                $category = 'Cukup Disiplin';
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
                'scores' => [
                    'attendance' => round($attendanceScore, 1),
                    'punctuality' => round($punctualityScore, 1),
                    'late_freq' => round($lateFreqScore, 1),
                    'alpha' => round($alphaScore, 1),
                    'consistency' => round($consistencyScore, 1),
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
