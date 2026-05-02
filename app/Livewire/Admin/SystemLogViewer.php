<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\File;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class SystemLogViewer extends Component
{
    public string $search = '';
    public string $levelFilter = '';
    public int $page = 1;
    public int $perPage = 50;

    protected function getLogPath(): string
    {
        return storage_path('logs/laravel.log');
    }

    protected function parseLogs(): array
    {
        $path = $this->getLogPath();

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $lines = explode("\n", $content);
        $entries = [];
        $currentEntry = null;

        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]\s+(\w+)\./', $line, $matches)) {
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }
                $currentEntry = [
                    'datetime' => $matches[1],
                    'level' => strtoupper($matches[2]),
                    'message' => $line,
                ];
            } elseif ($currentEntry) {
                $currentEntry['message'] .= "\n" . $line;
            }
        }

        if ($currentEntry) {
            $entries[] = $currentEntry;
        }

        return array_reverse($entries);
    }

    protected function filterEntries(array $entries): array
    {
        return array_filter($entries, function ($entry) {
            if ($this->levelFilter && $entry['level'] !== strtoupper($this->levelFilter)) {
                return false;
            }
            if ($this->search && ! str_contains(strtolower($entry['message']), strtolower($this->search))) {
                return false;
            }
            return true;
        });
    }

    public function updatingSearch(): void
    {
        $this->page = 1;
    }

    public function updatingLevelFilter(): void
    {
        $this->page = 1;
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function download(): void
    {
        $path = $this->getLogPath();
        if (File::exists($path)) {
            $this->dispatch('download-log', content: File::get($path));
        }
    }

    public function clear(): void
    {
        $path = $this->getLogPath();
        if (File::exists($path)) {
            File::put($path, '');
        }
        $this->page = 1;
        $this->dispatch('notify', type: 'success', message: 'Log berhasil dikosongkan.');
    }

    protected function getPaginationRange(int $totalPages): array
    {
        if ($totalPages <= 7) {
            return range(1, $totalPages);
        }

        $current = $this->page;
        $range = [];

        if ($current > 3) {
            $range[] = 1;
            $range[] = null;
        }

        $start = max(2, $current - 2);
        $end = min($totalPages - 1, $current + 2);

        for ($i = $start; $i <= $end; $i++) {
            $range[] = $i;
        }

        if ($current < $totalPages - 2) {
            $range[] = null;
            $range[] = $totalPages;
        }

        return $range;
    }

    public function goToPage(int $page): void
    {
        $this->page = $page;
    }

    public function render()
    {
        $allEntries = $this->parseLogs();
        $filtered = $this->filterEntries($allEntries);
        $total = count($filtered);
        $totalPages = (int) ceil($total / $this->perPage);
        $offset = ($this->page - 1) * $this->perPage;
        $entries = array_slice($filtered, $offset, $this->perPage);

        return view('livewire.admin.system-log-viewer', [
            'entries' => $entries,
            'total' => $total,
            'totalPages' => $totalPages,
            'paginationRange' => $this->getPaginationRange($totalPages),
        ]);
    }
}
