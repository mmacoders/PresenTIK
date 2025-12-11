<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class LaporanExport implements FromCollection, WithEvents, WithCustomStartCell
{
    protected $attendances;
    protected $exportData; 
    protected $dateRows = []; 
    protected $tableHeaderRows = []; 
    protected $dataRows = []; 

    public function __construct($attendances)
    {
        $this->attendances = $attendances;
        $this->prepareData();
    }

    public function prepareData()
    {
        $this->exportData = collect();
        $rowPointer = 4;

        $grouped = $this->attendances->groupBy('tanggal');

        foreach ($grouped as $date => $items) {
            $dateStr = Carbon::parse($date)->translatedFormat('l, d F Y');

            $presensiGroup = $items->filter(function($item) {
                return stripos($item->status, 'Izin') === false;
            });
            $izinGroup = $items->filter(function($item) {
                return stripos($item->status, 'Izin') !== false;
            });

            // =============================
            //      PRESENSI & ABSEN
            // =============================
            if ($presensiGroup->isNotEmpty()) {

                // Header tanggal
                $this->exportData->push([
                    strtoupper($dateStr)." | PRESENSI & ABSEN",
                    '', '', '', '', '', '', '' // 8 columns
                ]);
                $this->dateRows[] = $rowPointer;
                $rowPointer++;

                // Table header
                $this->exportData->push([
                    'No','Nama','Role','Pangkat / NRP','Jabatan','Jam Masuk','Status','Ket'
                ]);
                $this->tableHeaderRows[] = $rowPointer;
                $rowPointer++;

                $no = 1;
                foreach ($presensiGroup as $item) {
                    $user = $item->user;
                    
                    // Map Role
                    $roleLabel = $user->role;
                    if ($roleLabel === 'user') $roleLabel = 'User';
                    elseif ($roleLabel === 'admin') $roleLabel = 'Admin';
                    elseif ($roleLabel === 'superadmin') $roleLabel = 'SuperAdmin';

                    $this->exportData->push([
                        $no,
                        $user->name ?? '-',
                        $roleLabel,
                        ($user->pangkat ?? '-') . ' / ' . ($user->nrp ?? '-'),
                        $user->jabatan ?? '-',
                        ($item->waktu_masuk && $item->waktu_masuk !== '-') 
                            ? Carbon::parse($item->waktu_masuk)->format('H:i:s') 
                            : '-',
                        $item->status,
                        $item->keterangan ?? '-'
                    ]);

                    $this->dataRows[] = $rowPointer;
                    $rowPointer++;
                    $no++;
                }

                // TOTAL KEHADIRAN PER HARI
                $totalHadir = $presensiGroup->count();
                $this->exportData->push([
                    '', "Total Kehadiran: $totalHadir", '', '', '', '', '', ''
                ]);
                $this->dataRows[] = $rowPointer;
                $rowPointer++;

                // Spacer row
                $this->exportData->push(['','','','','','','','']);
                $rowPointer++;
            }

            // =============================
            //             IZIN
            // =============================
            if ($izinGroup->isNotEmpty()) {

                // Header tanggal
                $this->exportData->push([
                    strtoupper($dateStr)." | IZIN",
                    '', '', '', '', '', '', ''
                ]);
                $this->dateRows[] = $rowPointer;
                $rowPointer++;

                // Table header (format seperti PDF)
                $this->exportData->push([
                    'No','Nama','Role','Pangkat / NRP','Jabatan','Status','Tanggal izin','Keterangan'
                ]);
                $this->tableHeaderRows[] = $rowPointer;
                $rowPointer++;

                $no = 1;
                foreach ($izinGroup as $item) {
                    $user = $item->user;
                    
                    // Map Role
                    $roleLabel = $user->role;
                    if ($roleLabel === 'user') $roleLabel = 'User';
                    elseif ($roleLabel === 'admin') $roleLabel = 'Admin';
                    elseif ($roleLabel === 'superadmin') $roleLabel = 'SuperAdmin';

                    // Format tanggal izin 2 baris (dari s/d)
                    if ($item->tanggal_mulai && $item->tanggal_selesai) {
                        $tanggalRange = "Dari " . Carbon::parse($item->tanggal_mulai)->translatedFormat('d m Y') 
                                       . "\nSampai " . Carbon::parse($item->tanggal_selesai)->translatedFormat('d m Y');
                    } else {
                        $tanggalRange = '-';
                    }

                    $this->exportData->push([
                        $no,
                        $user->name ?? '-',
                        $roleLabel,
                        ($user->pangkat ?? '-') . ' / ' . ($user->nrp ?? '-'),
                        $user->jabatan ?? '-',
                        "Izin " . ($item->kategori_izin ? "(" . $item->kategori_izin . ")" : ""),      // Status = "Izin (Category)"
                        $tanggalRange,      // Tanggal izin rentang
                        $item->keterangan ?? '-'
                    ]);

                    $this->dataRows[] = $rowPointer;
                    $rowPointer++;
                    $no++;
                }

                // Spacer row
                $this->exportData->push(['','','','','','','','']);
                $rowPointer++;
            }
        }
    }

    public function collection()
    {
        return $this->exportData;
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {

                $sheet = $event->sheet;

                // ----------------------------
                // TITLE ROWS (A1–A3)
                // ----------------------------
                // Update merge to column H (8 columns)
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 
                    "KEPOLISIAN NEGARA REPUBLIK INDONESIA DAERAH GORONTALO\nBIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI"
                );
                $sheet->getStyle('A1')->applyFromArray([
                    'font'=>['bold'=>true,'size'=>12],
                    'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER,'wrapText'=>true]
                ]);

                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2', "ABSEN APEL PAGI / SIANG PERSONIL POLRI / PNS");
                $sheet->getStyle('A2')->applyFromArray([
                    'font'=>['bold'=>true,'size'=>12,'underline'=>true],
                    'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER]
                ]);

                $dates = $this->attendances->pluck('tanggal')->unique()->sort();
                $minDate = $dates->first() ? Carbon::parse($dates->first())->translatedFormat('d F Y') : '-';
                $maxDate = $dates->last() ? Carbon::parse($dates->last())->translatedFormat('d F Y') : '-';
                $periodStr = ($minDate === $maxDate) ? $minDate : "$minDate s/d $maxDate";

                $sheet->mergeCells('A3:H3');
                $sheet->setCellValue('A3', "PERIODE: " . strtoupper($periodStr));
                $sheet->getStyle('A3')->applyFromArray([
                    'font'=>['bold'=>true,'size'=>11],
                    'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER]
                ]);

                // ----------------------------
                // STYLE: DATE HEADERS
                // ----------------------------
                foreach ($this->dateRows as $row) {
                    $sheet->mergeCells("A{$row}:H{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font'=>['bold'=>true],
                        'fill'=>[
                            'fillType'=>Fill::FILL_SOLID,
                            'startColor'=>['rgb'=>'E0E0E0']
                        ],
                        'alignment'=>[
                            'horizontal'=>Alignment::HORIZONTAL_LEFT,
                            'vertical'=>Alignment::VERTICAL_CENTER,
                            'indent'=>1,
                            'wrapText'=>true
                        ],
                        'borders'=>[
                            'outline'=>['borderStyle'=>Border::BORDER_THIN]
                        ]
                    ]);
                }

                // ----------------------------
                // STYLE: TABLE HEADERS
                // ----------------------------
                foreach ($this->tableHeaderRows as $row) {
                    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                        'font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],
                        'fill'=>[
                            'fillType'=>Fill::FILL_SOLID,
                            'startColor'=>['rgb'=>'444444']
                        ],
                        'alignment'=>[
                            'horizontal'=>Alignment::HORIZONTAL_CENTER,
                            'vertical'=>Alignment::VERTICAL_CENTER,
                            'wrapText'=>true
                        ],
                        'borders'=>[
                            'allBorders'=>['borderStyle'=>Border::BORDER_THIN]
                        ]
                    ]);
                }

                // ----------------------------
                // STYLE: DATA ROWS
                // ----------------------------
                foreach ($this->dataRows as $row) {
                    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                        'borders'=>[
                            'allBorders'=>['borderStyle'=>Border::BORDER_THIN]
                        ],
                        'alignment'=>[
                            'vertical'=>Alignment::VERTICAL_CENTER,
                            'wrapText'=>true
                        ]
                    ]);

                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
                    $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Role
                    $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Jam
                    $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status
                }

                // ----------------------------
                // FIXED COLUMN WIDTHS (NO AUTOSIZE)
                // ----------------------------
                $widths = [
                    'A' => 6,  // No
                    'B' => 28, // Nama
                    'C' => 12, // Role (New)
                    'D' => 22, // Pangkat
                    'E' => 18, // Jabatan
                    'F' => 18, // Jam / Tgl
                    'G' => 22, // Status
                    'H' => 30, // Ket
                ];

                foreach ($widths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }
            }
        ];
    }
}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                