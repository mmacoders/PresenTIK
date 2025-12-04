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

    public function map($row): array
    {
        $this->rowNumber++;
        
        $user = $row->user;
        
        return [
            $this->rowNumber,
            $user->name ?? '-',
            ($user->pangkat ?? '-') . ' / ' . ($user->nrp ?? '-'),
            $user->jabatan ?? '-',
            $row->waktu_masuk ?? '-',
            $row->status, // Status is already processed in Controller
            $row->keterangan ?? '-',
        ];
    }

    // getStatusText is no longer needed as status is pre-processed, but keeping it empty or removing usage
    private function getStatusText($row)
    {
        return $row->status;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                
                // Merge title
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', "KEPOLISIAN NEGARA REPUBLIK INDONESIA DAERAH GORONTALO\nBIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI\nABSEN APEL PAGI / SIANG PERSONIL POLRI / PNS");
                $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
                
                // Style Title
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true, 
                        'size' => 12,
                        'color' => ['rgb' => '000000']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(60);

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
