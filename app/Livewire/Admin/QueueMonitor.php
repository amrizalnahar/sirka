<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class QueueMonitor extends Component
{
    public string $tab = 'jobs';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function retryFailedJob(int $id): void
    {
        $job = DB::table('failed_jobs')->find($id);

        if (! $job) {
            $this->dispatch('notify', type: 'error', message: 'Job tidak ditemukan.');
            return;
        }

        \Illuminate\Support\Facades\Artisan::call('queue:retry', ['id' => $job->uuid]);

        $this->dispatch('notify', type: 'success', message: 'Job failed berhasil di-retry.');
    }

    public function retryAllFailedJobs(): void
    {
        $count = DB::table('failed_jobs')->count();

        if ($count === 0) {
            $this->dispatch('notify', type: 'warning', message: 'Tidak ada failed jobs untuk di-retry.');
            return;
        }

        \Illuminate\Support\Facades\Artisan::call('queue:retry', ['id' => 'all']);

        $this->dispatch('notify', type: 'success', message: "{$count} failed jobs berhasil di-retry.");
    }

    public function deleteFailedJob(int $id): void
    {
        DB::table('failed_jobs')->where('id', $id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Job failed berhasil dihapus.');
    }

    public function flushFailedJobs(): void
    {
        DB::table('failed_jobs')->truncate();
        $this->dispatch('notify', type: 'success', message: 'Semua failed jobs berhasil dihapus.');
    }

    public function deletePendingJob(int $id): void
    {
        DB::table('jobs')->where('id', $id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Pending job berhasil dihapus.');
    }

    public function flushPendingJobs(): void
    {
        DB::table('jobs')->truncate();
        $this->dispatch('notify', type: 'success', message: 'Semua pending jobs berhasil dihapus.');
    }

    public function getJobsProperty(): array
    {
        $jobs = DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return $jobs->map(function ($job) {
            $payload = json_decode($job->payload, true);

            return [
                'id' => $job->id,
                'queue' => $job->queue,
                'display_name' => $payload['displayName'] ?? 'Unknown',
                'attempts' => $job->attempts,
                'created_at' => $job->created_at ? \Carbon\Carbon::createFromTimestamp($job->created_at)->diffForHumans() : '-',
                'created_at_exact' => $job->created_at ? \Carbon\Carbon::createFromTimestamp($job->created_at)->format('d M Y H:i:s') : '-',
            ];
        })->toArray();
    }

    public function getJobBatchesProperty(): array
    {
        if (! DB::getSchemaBuilder()->hasTable('job_batches')) {
            return [];
        }

        $batches = DB::table('job_batches')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return $batches->map(function ($batch) {
            return [
                'id' => $batch->id,
                'name' => $batch->name,
                'total_jobs' => $batch->total_jobs,
                'pending_jobs' => $batch->pending_jobs,
                'failed_jobs' => $batch->failed_jobs,
                'progress' => $batch->total_jobs > 0
                    ? round((($batch->total_jobs - $batch->pending_jobs) / $batch->total_jobs) * 100)
                    : 0,
                'cancelled_at' => $batch->cancelled_at
                    ? \Carbon\Carbon::createFromTimestamp($batch->cancelled_at)->format('d M Y H:i:s')
                    : null,
                'created_at' => $batch->created_at
                    ? \Carbon\Carbon::createFromTimestamp($batch->created_at)->format('d M Y H:i:s')
                    : '-',
                'finished_at' => $batch->finished_at
                    ? \Carbon\Carbon::createFromTimestamp($batch->finished_at)->format('d M Y H:i:s')
                    : null,
            ];
        })->toArray();
    }

    public function getFailedJobsProperty(): array
    {
        $jobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(100)
            ->get();

        return $jobs->map(function ($job) {
            $payload = json_decode($job->payload, true);

            return [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'display_name' => $payload['displayName'] ?? 'Unknown',
                'exception_preview' => $this->truncateException($job->exception, 150),
                'failed_at' => \Carbon\Carbon::parse($job->failed_at)->diffForHumans(),
                'failed_at_exact' => \Carbon\Carbon::parse($job->failed_at)->format('d M Y H:i:s'),
            ];
        })->toArray();
    }

    private function truncateException(?string $exception, int $length): string
    {
        if (! $exception) {
            return '-';
        }

        $lines = explode("\n", $exception);
        $firstLine = $lines[0] ?? '';

        return strlen($firstLine) > $length ? substr($firstLine, 0, $length) . '...' : $firstLine;
    }

    public function render()
    {
        return view('livewire.admin.queue-monitor');
    }
}
