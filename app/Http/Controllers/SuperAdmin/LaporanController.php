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
        $reportData = $this->getReportData($request);
        return Excel::download(new LaporanExport($reportData), 'laporan-absensi.xlsx');
    }
    
    public function exportPDF(Request $request)
    {
        $reportData = $this->getReportData($request);
        
        $now = Carbon::now();
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : $now->copy();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : $now->copy();

        $pdf = DomPdf::loadView('pdf.laporan_absensi', [
            'attendances' => $reportData,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);
        return $pdf->download('laporan-absensi.pdf');
    }

    private function getReportData(Request $request)
    {
        // Get date range
        $now = Carbon::now();
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : $now->copy();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : $now->copy();
        $search = $request->search;
        
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
        foreach ($permissions as $permission) {
            $pStart = Carbon::parse($permission->tanggal_mulai);
            $pEnd = Carbon::parse($permission->tanggal_selesai);
            
            // Clamp to requested range
            $loopStart = $pStart->max($startDate);
            $loopEnd = $pEnd->min($endDate);
            
            $current = $loopStart->copy();
            while ($current <= $loopEnd) {
                $dateStr = $current->format('Y-m-d');
                
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
        
        return $reportData->sortBy([
            ['tanggal', 'desc'],
            ['user.name', 'asc'],
        ]);
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