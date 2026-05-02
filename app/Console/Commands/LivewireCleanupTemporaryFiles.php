<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

class LivewireCleanupTemporaryFiles extends Command
{
    protected $signature = 'livewire:cleanup:temporary-files';

    protected $description = 'Cleanup temporary files uploaded by Livewire with logging';

    public function handle(): int
    {
        $startTime = microtime(true);
        $directory = 'livewire-tmp';
        $disk = Storage::disk('local');
        $fullPath = $disk->path($directory);

        if (! File::isDirectory($fullPath)) {
            Log::info('Livewire Cleanup Started. Directory not found.', [
                'directory' => $fullPath,
                'file_count' => 0,
            ]);
            $this->info('Directory not found: ' . $fullPath);

            return self::SUCCESS;
        }

        $files = File::files($fullPath);
        $fileCount = count($files);

        Log::info('Livewire Cleanup Started. Processing ' . $fileCount . ' files.', [
            'directory' => $fullPath,
            'file_count' => $fileCount,
        ]);
        $this->info('Livewire Cleanup Started. Processing ' . $fileCount . ' files.');

        $deletedCount = 0;
        $freedSize = 0;
        $failedCount = 0;

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $directory . '/' . $file->getFilename();
            $size = $file->getSize();
            $sizeFormatted = $this->formatBytes($size);

            try {
                if (File::delete($filePath)) {
                    $deletedCount++;
                    $freedSize += $size;

                    Log::info('Deleted file: ' . $relativePath . ' (' . $sizeFormatted . ')');
                    $this->info('Deleted: ' . $relativePath . ' (' . $sizeFormatted . ')');
                } else {
                    $failedCount++;

                    Log::warning('Failed to delete file: ' . $relativePath . ' (Delete returned false)');
                    $this->warn('Failed: ' . $relativePath);
                }
            } catch (\Exception $e) {
                $failedCount++;

                Log::warning('Failed to delete file: ' . $relativePath . ' (' . $e->getMessage() . ')');
                $this->warn('Failed: ' . $relativePath . ' - ' . $e->getMessage());
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $freedSizeFormatted = $this->formatBytes($freedSize);

        Log::info('Livewire Cleanup Completed. ' . $deletedCount . ' files deleted, ' . $freedSizeFormatted . ' freed in ' . $duration . 's.');
        $this->info('Livewire Cleanup Completed. ' . $deletedCount . ' files deleted, ' . $freedSizeFormatted . ' freed in ' . $duration . 's.');

        if ($failedCount > 0) {
            Log::warning('Livewire Cleanup finished with ' . $failedCount . ' failures.');
            $this->warn($failedCount . ' file(s) failed to delete.');
        }

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = (int) floor(log($bytes, 1024));
        $unitIndex = min($unitIndex, count($units) - 1);

        return round($bytes / (1024 ** $unitIndex), 2) . ' ' . $units[$unitIndex];
    }
}
