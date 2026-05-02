<?php

namespace App\Livewire\Laporan;

use App\Models\JenisLaporan;
use App\Models\Laporan;
use App\Models\LaporanItem;
use App\Models\PicConfig;
use App\Services\LaporanImportService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class LaporanImport extends Component
{
    use WithFileUploads;

    public $file = null;
    public ?int $jenis_laporan_id = null;
    public int $periode_bulan;
    public int $periode_tahun;
    public string $judul_laporan = '';
    public string $catatan_pic = '';

    public array $preview = [];
    public array $summary = ['total' => 0, 'errors' => 0, 'warnings' => 0, 'valid' => 0];
    public bool $showPreview = false;
    public bool $hasErrors = false;
    public string $parseError = '';

    public function mount(): void
    {
        $this->periode_bulan = (int) now()->format('n');
        $this->periode_tahun = (int) now()->format('Y');
    }

    public function parseFile(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
            'jenis_laporan_id' => ['required', 'exists:jenis_laporans,id'],
            'periode_bulan' => ['required', 'integer', 'between:1,12'],
            'periode_tahun' => ['required', 'integer', 'min:2020'],
            'judul_laporan' => ['required', 'string', 'max:255'],
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'Format file harus .xlsx atau .csv.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
            'jenis_laporan_id.required' => 'Jenis laporan wajib dipilih.',
            'judul_laporan.required' => 'Judul laporan wajib diisi.',
        ]);

        $picConfig = PicConfig::where('user_id', auth()->id())
            ->where('jenis_laporan_id', $this->jenis_laporan_id)
            ->first();

        if (! $picConfig) {
            $this->dispatch('notify', type: 'error', message: 'Anda tidak memiliki akses PIC untuk jenis laporan ini.');
            return;
        }

        $existing = Laporan::where('departemen_id', $picConfig->departemen_id)
            ->where('jenis_laporan_id', $this->jenis_laporan_id)
            ->where('periode_bulan', $this->periode_bulan)
            ->where('periode_tahun', $this->periode_tahun)
            ->whereNotIn('status', ['archived', 'rejected'])
            ->exists();

        if ($existing) {
            $this->dispatch('notify', type: 'error', message: 'Laporan untuk periode ini sudah ada dan masih aktif.');
            return;
        }

        $path = $this->file->getRealPath();

        $service = new LaporanImportService();
        $service->setContext($picConfig->departemen_id, $this->periode_bulan, $this->periode_tahun);
        $result = $service->parse($path);

        if (! empty($result['errors'])) {
            $this->parseError = implode(', ', $result['errors']);
            $this->showPreview = false;
            return;
        }

        $this->preview = $result['rows'];
        $this->summary = $result['summary'];
        $this->hasErrors = $result['summary']['errors'] > 0;
        $this->showPreview = true;
        $this->parseError = '';
    }

    public function save(): void
    {
        if ($this->hasErrors || empty($this->preview)) {
            $this->dispatch('notify', type: 'error', message: 'Tidak dapat menyimpan karena terdapat error.');
            return;
        }

        $picConfig = PicConfig::where('user_id', auth()->id())
            ->where('jenis_laporan_id', $this->jenis_laporan_id)
            ->first();

        if (! $picConfig) {
            $this->dispatch('notify', type: 'error', message: 'Anda tidak memiliki akses PIC.');
            return;
        }

        $laporan = null;

        DB::transaction(function () use ($picConfig, &$laporan) {
            $laporan = Laporan::create([
                'judul_laporan' => $this->judul_laporan,
                'departemen_id' => $picConfig->departemen_id,
                'jenis_laporan_id' => $this->jenis_laporan_id,
                'periode_bulan' => $this->periode_bulan,
                'periode_tahun' => $this->periode_tahun,
                'status' => 'draft',
                'catatan_pic' => $this->catatan_pic ?: null,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->preview as $row) {
                if ($row['status'] === 'error') {
                    continue;
                }

                LaporanItem::create(array_merge(
                    ['laporan_id' => $laporan->id],
                    $row['data']
                ));
            }
        });

        $this->dispatch('notify', type: 'success', message: 'Laporan berhasil disimpan.');
        $this->redirectRoute('admin.laporan.detail', ['laporan' => $laporan->id], navigate: true);
    }

    public function render()
    {
        $picJenisLaporanIds = PicConfig::where('user_id', auth()->id())
            ->pluck('jenis_laporan_id')
            ->filter()
            ->unique()
            ->values();

        return view('livewire.laporan.laporan-import', [
            'jenisLaporans' => JenisLaporan::active()
                ->whereIn('id', $picJenisLaporanIds)
                ->orderBy('nama')
                ->get(),
        ]);
    }
}
