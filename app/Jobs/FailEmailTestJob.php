<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FailEmailTestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $recipient
    ) {
    }

    public function handle(): void
    {
        throw new \Exception(
            "[SMTP TESTER] Simulasi kegagalan pengiriman email ke {$this->recipient}. " .
            "SMTP server merespons: 554 Transaction failed."
        );
    }
}
