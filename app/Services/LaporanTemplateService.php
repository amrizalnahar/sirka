<?php

namespace App\Services;

use App\Models\MasterAkun;
use App\Models\MasterKategori;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanTemplateService
{
    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;

        $this->buildDataSheet($spreadsheet->getActiveSheet());
        $this->buildReferenceSheet($spreadsheet->createSheet());

        return $spreadsheet;
    }

    protected function buildDataSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $sheet->setTitle('Data Kegiatan');

        $headers = [
            'kode_kegiatan',
            'nama_kegiatan',
            'kode_akun',
            'kode_kategori',
            'satuan',
            'volume_rencana',
            'volume_realisasi',
            'pagu_anggaran',
            'realisasi_anggaran',
            'tanggal_mulai',
            'tanggal_selesai',
            'status_kegiatan',
            'keterangan',
        ];

        // Header row
        foreach ($headers as $col => $header) {
            $cell = $sheet->getCell([$col + 1, 1]);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true);
            $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1A6FAA');
            $cell->getStyle()->getFont()->getColor()->setRGB('FFFFFF');
            $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Example data row
        $example = [
            'KG-001',
            'Pembangunan Jalan Desa Dusun I',
            '5.1.1.01',
            'INFRA',
            'meter',
            1200.00,
            850.00,
            500000000.00,
            350000000.00,
            '2026-01-15',
            '2026-06-30',
            'berlangsung',
            'Progres 70%, menunggu dana tambahan',
        ];

        foreach ($example as $col => $value) {
            $sheet->setCellValue([$col + 1, 2], $value);
        }

        // Auto width
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        // Data validation for status_kegiatan
        $validation = $sheet->getCell('L2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Value is not in list.');
        $validation->setPromptTitle('Pick from list');
        $validation->setPrompt('Please pick a value from the drop-down list.');
        $validation->setFormula1('"selesai,berlangsung,belum_dimulai"');
    }

    protected function buildReferenceSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $sheet->setTitle('Referensi');

        // Master Akun
        $sheet->setCellValue('A1', 'Master Akun');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->setCellValue('A2', 'kode_akun');
        $sheet->setCellValue('B2', 'nama');
        $sheet->getStyle('A2:B2')->getFont()->setBold(true);

        $akuns = MasterAkun::active()->orderBy('kode')->get();
        $row = 3;
        foreach ($akuns as $akun) {
            $sheet->setCellValue("A{$row}", $akun->kode);
            $sheet->setCellValue("B{$row}", $akun->nama);
            $row++;
        }

        // Master Kategori
        $catRow = 1;
        $sheet->setCellValue('D1', 'Master Kategori');
        $sheet->getStyle('D1')->getFont()->setBold(true);
        $sheet->setCellValue('D2', 'kode_kategori');
        $sheet->setCellValue('E2', 'nama');
        $sheet->getStyle('D2:E2')->getFont()->setBold(true);

        $kategoris = MasterKategori::active()->orderBy('kode')->get();
        $row = 3;
        foreach ($kategoris as $kat) {
            $sheet->setCellValue("D{$row}", $kat->kode);
            $sheet->setCellValue("E{$row}", $kat->nama);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
    }

    public function download(string $filename = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = $this->generate();
        $writer = new Xlsx($spreadsheet);

        $filename ??= 'template-sirka-' . now()->format('Ym') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}
