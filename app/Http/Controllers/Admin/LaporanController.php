<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Izin;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth()->user();
        
        // Get date range (default to current month) using Carbon with proper timezone
        $now = Carbon::now();
        $startDate = request('start_date', $now->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', $now->copy()->endOfMonth()->format('Y-m-d'));
        $search = request('search');
        
        // Get all users including admin and superadmin for monitoring
        $users = User::all();
        
        // --- Attendances Query ---
        $attendancesQuery = Absensi::whereBetween('tanggal', [$startDate, $endDate])
                    ->with('user')
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('created_at', 'desc');

        if ($search) {
            $attendancesQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }
        
        $attendances = $attendancesQuery->paginate(10, ['*'], 'attendances_page')->withQueryString();

        // --- Permissions (Izin) Query ---
        $permissionsQuery = Izin::with('user')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                  ->orWhereBetween('tanggal_selesai', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc');

        if ($search) {
            $permissionsQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $permissions = $permissionsQuery->paginate(10, ['*'], 'permissions_page')->withQueryString();
        
        // Set no-cache headers
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        return Inertia::render('Admin/Laporan', [
            'users' => $users,
            'attendances' => $attendances,
            'permissions' => $permissions,
            'filters' => $request->only(['start_date', 'end_date', 'search']),
        ]);
    }
    
    public function export(Request $request)
    {
        $admin = auth()->user();
        
        // Get date range
        $now = Carbon::now();
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : $now->copy();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : $now->copy();
        $search = $request->search;
        $format = $request->format;
        
        // Fetch Absensi records
        $attendancesQuery = Absensi::whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with('user');
            
        if ($search) {
            $attendancesQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }
        
        $attendances = $attendancesQuery->get();

        // Fetch Approved Izin records
        $permissionsQuery = Izin::with('user')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_mulai', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereBetween('tanggal_selesai', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            })
            ->whereIn('status', ['approved', 'disetujui']);

        if ($search) {
            $permissionsQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }
            
        $permissions = $permissionsQuery->get();
            
        // Process data into a collection
        $reportData = collect();
        
        // Add Absensi records
        foreach ($attendances as $attendance) {
            $reportObject = new \stdClass();
            $reportObject->user = $attendance->user;
            $reportObject->tanggal = $attendance->tanggal;
            $reportObject->waktu_masuk = $attendance->waktu_masuk ?? '-';
            $reportObject->waktu_keluar = $attendance->waktu_keluar ?? '-';
            $reportObject->status = $this->getStatusText($attendance);
            $reportObject->keterangan = $attendance->keterangan ?? '-';
            
            $reportData->push($reportObject);
        }

        // Add Izin records
        // Note: Izin might span multiple days. We should probably expand them if we want a daily report,
        // OR just list the izin record once. 
        // Given the previous requirement was "daily report", expanding seems correct, 
        // BUT the user said "only data in table". The table usually lists the Izin record once.
        // However, for a "Laporan Absensi" (Attendance Report), usually you want to see the status for each day.
        // But if I expand it, I might create "Alpha" records implicitly if I'm not careful? No, I'm only creating records from Izin.
        // Let's expand Izin into daily records to match the "Absensi" format, but ONLY for the days in the range.
        
        foreach ($permissions as $permission) {
            $pStart = Carbon::parse($permission->tanggal_mulai);
            $pEnd = Carbon::parse($permission->tanggal_selesai);
            
            // Clamp to requested range
            $loopStart = $pStart->max($startDate);
            $loopEnd = $pEnd->min($endDate);
            
            $current = $loopStart->copy();
            while ($current <= $loopEnd) {
                $dateStr = $current->format('Y-m-d');
                
                // Check if we already have an attendance record for this user on this day
                // (e.g. they checked in but also have a permission, or partial leave)
                // If the attendance record exists, it's already in $reportData.
                // We should avoid duplicates.
                $exists = $reportData->contains(function ($item) use ($permission, $dateStr) {
                    return $item->user->id === $permission->user_id && $item->tanggal === $dateStr;
                });
                
                if (!$exists) {
                    $reportObject = new \stdClass();
                    $reportObject->user = $permission->user;
                    $reportObject->tanggal = $dateStr;
                    $reportObject->waktu_masuk = '-';
                    $reportObject->waktu_keluar = '-';
                    $reportObject->status = $permission->catatan ?? 'Izin';
                    $reportObject->keterangan = $permission->keterangan ?? 'Izin Resmi';
                    
                    $reportData->push($reportObject);
                }
                
                $current->addDay();
            }
        }
        
        // Sort by date desc, then user name
        $reportData = $reportData->sortBy([
            ['tanggal', 'desc'],
            ['user.name', 'asc'],
        ]);

        if ($format === 'excel') {
            return Excel::download(new LaporanExport($reportData), 'laporan-absensi.xlsx');
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pdf.laporan_absensi', [
                'attendances' => $reportData,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d')
            ]);
            return $pdf->download('laporan-absensi.pdf');
        }
        
        // CSV Fallback
        $csvData = "Nama,Role,Tanggal,Masuk,Keluar,Status,Keterangan\n";
        
        foreach ($reportData as $row) {
            $csvData .= '"' . $row->user->name . '",';
            $csvData .= '"' . ucfirst($row->user->role) . '",';
            $csvData .= '"' . $row->tanggal . '",';
            $csvData .= '"' . $row->waktu_masuk . '",';
            $csvData .= '"' . $row->waktu_keluar . '",';
            $csvData .= '"' . $row->status . '",';
            $csvData .= '"' . $row->keterangan . '"' . "\n";
        }
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="laporan-absensi.csv"');
    }
    
    // Helper function to get status text
    private function getStatusText($attendance)
    {
        // Handle all possible status values from the database
        switch ($attendance->status) {
            case 'izin':
            case 'Izin':
            case 'Izin (Valid)':
                return 'Izin';
            case 'sakit':
                return 'Sakit';
            case 'terlambat':
            case 'Terlambat':
                return 'Terlambat';
            case 'hadir':
            case 'Hadir':
                return $attendance->waktu_masuk ? 'Hadir' : 'Belum Absen';
            case 'Izin Parsial (Check-in)':
                return 'Izin Parsial (Check-in)';
            case 'Izin Parsial (Selesai)':
                return 'Izin Parsial (Selesai)';
            case 'Tidak Hadir (Alpha)':
                return 'Tidak Hadir (Alpha)';
            default:
                // Handle any other status values or null status
                if ($attendance->waktu_masuk && $attendance->waktu_keluar) {
                    return 'Hadir';
                } else if ($attendance->waktu_masuk) {
                    return 'Sudah Check-in';
                } else {
                    return $attendance->status ?: 'Belum Absen';
                }
        }
    }
}