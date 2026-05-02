<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RsaEncryptionService;
use Illuminate\Console\Command;

class GenerateRsaKeys extends Command
{
    protected $signature = 'encryption:generate-keys';

    protected $description = 'Generate a new RSA key pair for password encryption';

    public function handle(RsaEncryptionService $service): int
    {
        $keys = $service->generateKeyPair();

        $this->info('RSA key pair generated successfully.');
        $this->newLine();
        $this->line("  Key ID:      {$keys['key_id']}");
        $this->line('  Public key:  storage/app/rsa-keys/' . $keys['key_id'] . '.public.pem');
        $this->line('  Private key: storage/app/rsa-keys/' . $keys['key_id'] . '.private.pem');
        $this->newLine();
        $this->warn('  Keep the private key secure. Never expose it to the client.');

        return self::SUCCESS;
    }
}
