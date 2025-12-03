<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithCustomStartCell
{
    protected $attendances;
    protected $rowNumber = 0;

    public function __construct($attendances)
    {
        $this->attendances = $attendances;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Pangkat / NRP',
            'Jabatan',
            'Jam Masuk',
            'Status',
            'Ket',
        ];
    }

    public function map($attendance): array
    {
        $this->rowNumber++;
        
        $user = $attendance->user;
        
        return [
            $this->rowNumber,
            $user->name ?? '-',
            ($user->pangkat ?? '-') . ' / ' . ($user->nrp ?? '-'),
            $user->jabatan ?? '-',
            $attendance->waktu_masuk ?? '-',
            $this->getStatusText($attendance),
            $attendance->keterangan ?? '-',
        ];
    }

    private function getStatusText($attendance)
    {
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
                if ($attendance->waktu_masuk && $attendance->waktu_keluar) {
                    return 'Hadir';
                } else if ($attendance->waktu_masuk) {
                    return 'Sudah Check-in';
                } else {
                    return $attendance->status ?: 'Belum Absen';
                }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                
                // Merge title
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'LAPORAN PRESENSI PERSONEL');
                
                // Style Title
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true, 
                        'size' => 14,
                        'color' => ['rgb' => '000000']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Style Header (Row 2)
                $sheet->getStyle('A2:G2')->applyFromArray([
                    'font' => [
                        'bold' => true, 
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '444444'], // Dark background
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // Style Data Table
                if ($highestRow >= 3) {
                    $dataRange = 'A3:G' . $highestRow;
                    
                    // All borders for data
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                    ]);

                    // Specific Alignments
                    // No (A) -> Center
                    $sheet->getStyle('A3:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // Jam Masuk (E) -> Center
                    $sheet->getStyle('E3:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // Status (F) -> Center
                    $sheet->getStyle('F3:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Thick Outline Border for the whole table
                $sheet->getStyle('A2:G' . $highestRow)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Auto size columns
                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
