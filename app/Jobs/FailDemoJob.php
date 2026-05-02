<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FailDemoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $reason = 'Demo failed job untuk testing Queue Monitor'
    ) {
    }

    public function handle(): void
    {
        throw new \Exception("[DEMO] {$this->reason} pada " . now()->format('d M Y H:i:s'));
    }
}
