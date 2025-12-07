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
    protected $exportData; // Flattened data for excel
    protected $dateRows = []; // Indices of date header rows
    protected $tableHeaderRows = []; // Indices of table header rows
    protected $dataRows = []; // Indices of data rows

    public function __construct($attendances)
    {
        $this->attendances = $attendances;
        $this->prepareData();
    }

    public function prepareData()
    {
        $this->exportData = collect();
        $rowPointer = 4; // Start at A4 (Title A1-A3)

        // Group by Tanggal
        $grouped = $this->attendances->groupBy('tanggal');
        
        foreach ($grouped as $date => $items) {
            // 1. Date Header Row
            $dateStr = Carbon::parse($date)->translatedFormat('l, d F Y');
            $this->exportData->push([strtoupper($dateStr)]);
            $this->dateRows[] = $rowPointer;
            $rowPointer++;

            // 2. Table Header Row
            $this->exportData->push(['No', 'Nama', 'Pangkat / NRP', 'Jabatan', 'Jam Masuk', 'Status', 'Ket']);
            $this->tableHeaderRows[] = $rowPointer;
            $rowPointer++;

            // 3. Data Rows
            $no = 1;
            foreach ($items as $item) {
                $user = $item->user;
                $this->exportData->push([
                    $no,
                    $user->name ?? '-',
                    ($user->pangkat ?? '-') . ' / ' . ($user->nrp ?? '-'),
                    $user->jabatan ?? '-',
                    $item->waktu_masuk ?? '-',
                    $item->status,
                    $item->keterangan ?? '-',
                ]);
                $this->dataRows[] = $rowPointer;
                $rowPointer++;
                $no++;
            }
            
            // 4. Spacer Row
            $this->exportData->push(['']);
            $rowPointer++;
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
                $highestRow = $sheet->getHighestRow();
                
                // --- Row 1-3: Main Title ---
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', "KEPOLISIAN NEGARA REPUBLIK INDONESIA DAERAH GORONTALO\nBIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(40);

                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', "ABSEN APEL PAGI / SIANG PERSONIL POLRI / PNS");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'underline' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                // Determine Period from Data
                $dates = $this->attendances->pluck('tanggal')->unique()->sort();
                $minDate = $dates->first() ? Carbon::parse($dates->first())->translatedFormat('d F Y') : '-';
                $maxDate = $dates->last() ? Carbon::parse($dates->last())->translatedFormat('d F Y') : '-';
                $periodStr = ($minDate === $maxDate) ? $minDate : "$minDate s/d $maxDate";

                $sheet->mergeCells('A3:G3');
                $sheet->setCellValue('A3', "PERIODE: " . strtoupper($periodStr));
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                // --- Style Date Headers ---
                foreach ($this->dateRows as $row) {
                    $sheet->mergeCells("A{$row}:G{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                        'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN]]
                    ]);
                }

                // --- Style Table Headers ---
                foreach ($this->tableHeaderRows as $row) {
                    $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '444444']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    ]);
                }

                // --- Style Data Rows ---
                foreach ($this->dataRows as $row) {
                    $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    // Center specific columns
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
                    $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Jam
                    $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status
                }

                // Auto size columns
                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
