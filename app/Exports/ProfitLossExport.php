<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithColumnFormatting
{
    public function __construct(
        private readonly array $data,
        private readonly User  $user,
    ) {}

    public function collection()
    {
        return collect($this->data['by_category'] ?? []);
    }

    public function headings(): array
    {
        return [
            'Kategori',
            'Tipe',
            'Jumlah Transaksi',
            'Total (Rp)',
            'Rata-rata (Rp)',
        ];
    }

    public function map($row): array
    {
        return [
            $row['category_name'],
            $row['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran',
            $row['transaction_count'],
            (float) $row['total_amount'],
            round((float) $row['avg_amount'], 2),
        ];
    }

    public function title(): string
    {
        return 'Laporan ' . ($this->data['period'] ?? 'Periode');
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF6366F1']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 5);

                $sheet->setCellValue('A1', $this->user->business_name);
                $sheet->setCellValue('A2', 'Laporan Keuangan: ' . ($this->data['period'] ?? ''));
                $sheet->setCellValue('A3', 'Periode: ' . ($this->data['date_from'] ?? '') . ' s/d ' . ($this->data['date_to'] ?? ''));
                $sheet->setCellValue('A4', 'Dibuat: ' . now()->translatedFormat('d F Y H:i'));

                $sheet->setCellValue('A6', 'Total Pemasukan');
                $sheet->setCellValue('B6', (float) ($this->data['gross_income'] ?? 0));
                $sheet->setCellValue('A7', 'Total Pengeluaran');
                $sheet->setCellValue('B7', (float) ($this->data['total_expense'] ?? 0));
                $sheet->setCellValue('A8', 'Laba Bersih');
                $sheet->setCellValue('B8', (float) ($this->data['net_profit'] ?? 0));
                $sheet->setCellValue('A9', 'Margin Laba');
                $sheet->setCellValue('B9', ($this->data['profit_margin'] ?? 0) . '%');

                // Style summary
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A6:B9')->getFont()->setBold(true);
                $sheet->getStyle('B6:B8')->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            },
        ];
    }
}
