<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;

use App\Models\Izin;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Build query for attendances
        $attendancesQuery = Absensi::with(['user'])->orderBy('created_at', 'desc');
        
        if ($request->search) {
            $attendancesQuery->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->start_date) {
            $attendancesQuery->where('tanggal', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $attendancesQuery->where('tanggal', '<=', $request->end_date);
        }
        
        // Paginate results
        $attendances = $attendancesQuery->paginate(10, ['*'], 'attendances_page')->withQueryString();

        // Build query for permissions
        $permissionsQuery = Izin::with(['user'])->orderBy('created_at', 'desc');

        if ($request->search) {
            $permissionsQuery->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->start_date) {
            $permissionsQuery->where('tanggal_mulai', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $permissionsQuery->where('tanggal_selesai', '<=', $request->end_date);
        }

        $permissions = $permissionsQuery->paginate(10, ['*'], 'permissions_page')->withQueryString();
        
        return Inertia::render('SuperAdmin/LaporanGlobal', [
            'attendances' => $attendances,
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'start_date', 'end_date']),
        ]);
    }
    
    public function exportExcel(Request $request)
    {
        // Build query for attendances
        $attendancesQuery = Absensi::with(['user'])->orderBy('created_at', 'desc');
        
        if ($request->search) {
            $attendancesQuery->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Default date range if not provided
        $now = Carbon::now();
        $startDate = $request->start_date ?: $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?: $now->copy()->endOfMonth()->format('Y-m-d');

        $attendancesQuery->whereBetween('tanggal', [$startDate, $endDate]);
        
        // Get all results
        $attendances = $attendancesQuery->get();
        
        return Excel::download(new LaporanExport($attendances), 'laporan-absensi.xlsx');
    }
    
    public function exportPDF(Request $request)
    {
        // Build query for attendances
        $attendancesQuery = Absensi::with(['user'])->orderBy('created_at', 'desc');
        
        if ($request->search) {
            $attendancesQuery->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->start_date) {
            $attendancesQuery->where('tanggal', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $attendancesQuery->where('tanggal', '<=', $request->end_date);
        }
        
        // Get all results
        $attendances = $attendancesQuery->get();
        
        // Default date range if not provided
        $now = Carbon::now();
        $startDate = $request->start_date ?: $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?: $now->copy()->endOfMonth()->format('Y-m-d');

        $pdf = DomPdf::loadView('pdf.laporan_absensi', [
            'attendances' => $attendances,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        return $pdf->download('laporan-absensi.pdf');
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