<?php

namespace App\Services;

use App\Models\Laporan;
use App\Models\MasterAkun;
use App\Models\MasterKategori;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LaporanImportService
{
    protected array $requiredColumns = [
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

    protected ?int $departemenId = null;
    protected ?int $periodeBulan = null;
    protected ?int $periodeTahun = null;

    public function setContext(int $departemenId, int $periodeBulan, int $periodeTahun): self
    {
        $this->departemenId = $departemenId;
        $this->periodeBulan = $periodeBulan;
        $this->periodeTahun = $periodeTahun;

        return $this;
    }

    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            return [
                'rows' => [],
                'summary' => ['total' => 0, 'errors' => 0, 'warnings' => 0, 'valid' => 0],
                'errors' => ['File kosong atau tidak memiliki data.'],
            ];
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $missingColumns = array_diff($this->requiredColumns, $headers);

        if (! empty($missingColumns)) {
            return [
                'rows' => [],
                'summary' => ['total' => 0, 'errors' => 0, 'warnings' => 0, 'valid' => 0],
                'errors' => ['Kolom wajib tidak ditemukan: ' . implode(', ', $missingColumns)],
            ];
        }

        $headerMap = array_flip($headers);
        $dataRows = [];
        $kodeKegiatanInFile = [];

        // First pass: collect all kode_kegiatan for duplicate check
        for ($i = 1; $i < count($rows); $i++) {
            $kode = trim((string) ($rows[$i][$headerMap['kode_kegiatan']] ?? ''));
            if ($kode !== '') {
                if (! isset($kodeKegiatanInFile[$kode])) {
                    $kodeKegiatanInFile[$kode] = [];
                }
                $kodeKegiatanInFile[$kode][] = $i + 1;
            }
        }

        $existingKodes = [];
        if ($this->departemenId && $this->periodeBulan && $this->periodeTahun) {
            $existingKodes = Laporan::where('departemen_id', $this->departemenId)
                ->where('periode_bulan', $this->periodeBulan)
                ->where('periode_tahun', $this->periodeTahun)
                ->whereIn('status', ['draft', 'submitted', 'revision', 'approved_1', 'approved_2'])
                ->with('items')
                ->get()
                ->flatMap(fn ($laporan) => $laporan->items->pluck('kode_kegiatan'))
                ->unique()
                ->values()
                ->all();
        }

        $masterAkunCodes = MasterAkun::active()->pluck('kode')->all();
        $masterKategoriCodes = MasterKategori::active()->pluck('kode')->all();

        $summary = ['total' => 0, 'errors' => 0, 'warnings' => 0, 'valid' => 0];

        for ($i = 1; $i < count($rows); $i++) {
            $rowNum = $i + 1;
            $raw = $rows[$i];

            // Skip completely empty rows
            if (empty(array_filter($raw, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $data = [];
            foreach ($this->requiredColumns as $col) {
                $data[$col] = $raw[$headerMap[$col]] ?? null;
            }

            $result = $this->validateRow($data, $rowNum, $kodeKegiatanInFile, $existingKodes, $masterAkunCodes, $masterKategoriCodes);
            $dataRows[] = $result;
            $summary['total']++;

            if ($result['status'] === 'error') {
                $summary['errors']++;
            } elseif ($result['status'] === 'warning') {
                $summary['warnings']++;
            } else {
                $summary['valid']++;
            }
        }

        return [
            'rows' => $dataRows,
            'summary' => $summary,
            'errors' => [],
        ];
    }

    protected function validateRow(array $data, int $rowNum, array $kodeKegiatanInFile, array $existingKodes, array $masterAkunCodes, array $masterKategoriCodes): array
    {
        $errors = [];
        $warnings = [];

        $kodeKegiatan = trim((string) ($data['kode_kegiatan'] ?? ''));
        $namaKegiatan = trim((string) ($data['nama_kegiatan'] ?? ''));
        $kodeAkun = trim((string) ($data['kode_akun'] ?? ''));
        $kodeKategori = trim((string) ($data['kode_kategori'] ?? ''));
        $satuan = trim((string) ($data['satuan'] ?? ''));
        $volumeRencana = $data['volume_rencana'];
        $volumeRealisasi = $data['volume_realisasi'];
        $paguAnggaran = $data['pagu_anggaran'];
        $realisasiAnggaran = $data['realisasi_anggaran'];
        $tanggalMulai = $data['tanggal_mulai'];
        $tanggalSelesai = $data['tanggal_selesai'];
        $statusKegiatan = strtolower(trim((string) ($data['status_kegiatan'] ?? '')));
        $keterangan = trim((string) ($data['keterangan'] ?? ''));

        // kode_kegiatan
        if ($kodeKegiatan === '') {
            $errors['kode_kegiatan'] = 'Kode kegiatan wajib diisi.';
        } elseif (! preg_match('/^[A-Z0-9_-]{1,20}$/i', $kodeKegiatan)) {
            $errors['kode_kegiatan'] = 'Kode kegiatan hanya boleh huruf, angka, strip, dan underscore (maks 20 karakter).';
        } elseif (count($kodeKegiatanInFile[$kodeKegiatan] ?? []) > 1) {
            $errors['kode_kegiatan'] = "Kode kegiatan '{$kodeKegiatan}' duplikat dalam file (baris " . implode(', ', $kodeKegiatanInFile[$kodeKegiatan]) . ').';
        } elseif (in_array($kodeKegiatan, $existingKodes)) {
            $errors['kode_kegiatan'] = "Kode kegiatan '{$kodeKegiatan}' sudah tersimpan untuk periode {$this->periodeBulan}/{$this->periodeTahun}.";
        }

        // nama_kegiatan
        if ($namaKegiatan === '') {
            $errors['nama_kegiatan'] = 'Nama kegiatan wajib diisi.';
        }

        // kode_akun
        if ($kodeAkun === '') {
            $errors['kode_akun'] = 'Kode akun wajib diisi.';
        } elseif (! in_array($kodeAkun, $masterAkunCodes)) {
            $errors['kode_akun'] = "Kode akun '{$kodeAkun}' tidak ditemukan di master. Lihat sheet 'Referensi' pada template.";
        }

        // kode_kategori
        if ($kodeKategori === '') {
            $errors['kode_kategori'] = 'Kode kategori wajib diisi.';
        } elseif (! in_array($kodeKategori, $masterKategoriCodes)) {
            $errors['kode_kategori'] = "Kode kategori '{$kodeKategori}' tidak ditemukan di master. Lihat sheet 'Referensi' pada template.";
        }

        // satuan
        if ($satuan === '') {
            $errors['satuan'] = 'Satuan wajib diisi.';
        }

        // volume_rencana
        $vr = $this->parseNumeric($volumeRencana);
        if ($vr === null || $vr <= 0) {
            $errors['volume_rencana'] = 'Volume rencana harus angka > 0.';
        }

        // volume_realisasi
        $vreal = $this->parseNumeric($volumeRealisasi);
        if ($vreal === null || $vreal < 0) {
            $errors['volume_realisasi'] = 'Volume realisasi harus angka >= 0.';
        } elseif ($vreal !== null && $vr !== null && $vreal > $vr) {
            $warnings['volume_realisasi'] = 'Volume realisasi melebihi volume rencana.';
        }

        // pagu_anggaran
        $pagu = $this->parseNumeric($paguAnggaran);
        if ($pagu === null || $pagu < 0) {
            $errors['pagu_anggaran'] = 'Pagu anggaran harus angka >= 0.';
        } elseif (is_string($paguAnggaran) && $this->containsCurrencyFormat((string) $paguAnggaran)) {
            $errors['pagu_anggaran'] = 'Format angka tidak valid. Hapus Rp, titik, atau koma ribuan.';
        }

        // realisasi_anggaran
        $real = $this->parseNumeric($realisasiAnggaran);
        if ($real === null || $real < 0) {
            $errors['realisasi_anggaran'] = 'Realisasi anggaran harus angka >= 0.';
        } elseif (is_string($realisasiAnggaran) && $this->containsCurrencyFormat((string) $realisasiAnggaran)) {
            $errors['realisasi_anggaran'] = 'Format angka tidak valid. Hapus Rp, titik, atau koma ribuan.';
        } elseif ($real !== null && $pagu !== null && $real > $pagu) {
            $warnings['realisasi_anggaran'] = 'Realisasi anggaran melebihi pagu anggaran.';
        }

        // tanggal_mulai
        $tglMulai = $this->parseDate($tanggalMulai);
        if ($tglMulai === null) {
            $errors['tanggal_mulai'] = 'Tanggal mulai tidak valid (format: YYYY-MM-DD).';
        }

        // tanggal_selesai
        $tglSelesai = null;
        if ($tanggalSelesai !== null && $tanggalSelesai !== '') {
            $tglSelesai = $this->parseDate($tanggalSelesai);
            if ($tglSelesai === null) {
                $errors['tanggal_selesai'] = 'Tanggal selesai tidak valid (format: YYYY-MM-DD).';
            } elseif ($tglMulai !== null && $tglSelesai < $tglMulai) {
                $errors['tanggal_selesai'] = 'Tanggal selesai harus >= tanggal mulai.';
            }
        }

        // status_kegiatan
        $validStatuses = ['selesai', 'berlangsung', 'belum_dimulai'];
        if (! in_array($statusKegiatan, $validStatuses)) {
            $errors['status_kegiatan'] = 'Status kegiatan harus: selesai, berlangsung, atau belum_dimulai.';
        } else {
            if ($statusKegiatan === 'selesai' && $real !== null && $real <= 0) {
                $errors['status_kegiatan'] = 'Status selesai harus memiliki realisasi anggaran > 0.';
            }
            if ($statusKegiatan === 'belum_dimulai' && $real !== null && $real > 0) {
                $warnings['status_kegiatan'] = 'Status belum_dimulai seharusnya memiliki realisasi anggaran = 0.';
            }
        }

        // keterangan length
        if (mb_strlen($keterangan) > 500) {
            $warnings['keterangan'] = 'Keterangan melebihi 500 karakter.';
        }

        $status = empty($errors) ? (empty($warnings) ? 'valid' : 'warning') : 'error';

        return [
            'row_num' => $rowNum,
            'data' => [
                'kode_kegiatan' => $kodeKegiatan,
                'nama_kegiatan' => $namaKegiatan,
                'kode_akun' => $kodeAkun,
                'kode_kategori' => $kodeKategori,
                'satuan' => $satuan,
                'volume_rencana' => $vr,
                'volume_realisasi' => $vreal,
                'pagu_anggaran' => $pagu,
                'realisasi_anggaran' => $real,
                'tanggal_mulai' => $tglMulai?->format('Y-m-d'),
                'tanggal_selesai' => $tglSelesai?->format('Y-m-d'),
                'status_kegiatan' => $statusKegiatan,
                'keterangan' => $keterangan,
            ],
            'status' => $status,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    protected function parseNumeric(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $cleaned = preg_replace('/[^\d.-]/', '', str_replace(',', '.', $value));
            if (is_numeric($cleaned)) {
                return (float) $cleaned;
            }
        }

        return null;
    }

    protected function containsCurrencyFormat(string $value): bool
    {
        return (bool) preg_match('/[Rr][Pp]|[.,](\d{3})/', $value);
    }

    protected function parseDate(mixed $value): ?\Carbon\Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            return \Carbon\Carbon::parse($value);
        }

        if (is_string($value) || is_numeric($value)) {
            $str = (string) $value;
            // Excel serial date
            if (is_numeric($str)) {
                try {
                    return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $str));
                } catch (\Exception $e) {
                    return null;
                }
            }

            $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y'];
            foreach ($formats as $format) {
                try {
                    return \Carbon\Carbon::createFromFormat($format, $str);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }
}
