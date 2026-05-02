<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FailDemoJob;
use Illuminate\Console\Command;

class QueueDemoFail extends Command
{
    protected $signature = 'queue:demo-fail {count=1 : Jumlah failed jobs yang akan dibuat}';

    protected $description = 'Membuat sample failed jobs untuk testing Queue Monitor';

    public function handle(): int
    {
        $count = (int) $this->argument('count');

        if ($count < 1) {
            $this->error('Jumlah minimal 1.');
            return self::FAILURE;
        }

        $reasons = [
            'Koneksi SMTP timeout',
            'Gagal mengirim notifikasi email',
            'Database connection lost',
            'Memory limit exceeded',
            'Third-party API tidak merespons',
            'File attachment tidak ditemukan',
            'Rate limit exceeded',
        ];

        for ($i = 0; $i < $count; $i++) {
            $reason = $reasons[array_rand($reasons)];
            $jobNum = $i + 1;
            FailDemoJob::dispatch("{$reason} (job #{$jobNum})");
        }

        $this->info("{$count} demo job(s) berhasil didispatch ke queue.");
        $this->warn("Jalankan \"php artisan queue:work\" agar job diproses dan masuk ke failed_jobs.");

        return self::SUCCESS;
    }
}
