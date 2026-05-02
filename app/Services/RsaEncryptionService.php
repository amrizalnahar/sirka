<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use phpseclib3\Crypt\RSA;

class RsaEncryptionService
{
    private string $keysPath;

    public function __construct()
    {
        $this->keysPath = storage_path('app/rsa-keys');
    }

    public function generateKeyPair(): array
    {
        $key = RSA::createKey(2048);
        $privateKey = $key;
        $publicKey = $key->getPublicKey();

        $privateKey = $privateKey->withPadding(RSA::ENCRYPTION_OAEP)->withHash('sha256');
        $publicKey = $publicKey->withPadding(RSA::ENCRYPTION_OAEP)->withHash('sha256');

        $keyId = (string) Str::uuid();

        $this->saveKey($keyId, (string) $key, (string) $publicKey);

        return [
            'key_id' => $keyId,
            'public_key' => (string) $publicKey,
            'private_key' => (string) $key,
        ];
    }

    public function saveKey(string $keyId, string $privateKey, string $publicKey): void
    {
        if (! is_dir($this->keysPath)) {
            mkdir($this->keysPath, 0700, true);
        }

        file_put_contents("{$this->keysPath}/{$keyId}.private.pem", $privateKey);
        file_put_contents("{$this->keysPath}/{$keyId}.public.pem", $publicKey);
    }

    public function getPublicKey(string $keyId): ?string
    {
        $path = "{$this->keysPath}/{$keyId}.public.pem";

        return file_exists($path) ? file_get_contents($path) : null;
    }

    public function getPrivateKey(string $keyId): ?RSA\PrivateKey
    {
        $path = "{$this->keysPath}/{$keyId}.private.pem";

        if (! file_exists($path)) {
            return null;
        }

        $pem = file_get_contents($path);
        $key = RSA::loadPrivateKey($pem);

        return $key->withPadding(RSA::ENCRYPTION_OAEP)->withHash('sha256');
    }

    public function decrypt(string $base64Payload, string $keyId): ?array
    {
        $privateKey = $this->getPrivateKey($keyId);

        if (! $privateKey) {
            return null;
        }

        $encrypted = base64_decode($base64Payload, true);

        if ($encrypted === false) {
            return null;
        }

        try {
            $decrypted = $privateKey->decrypt($encrypted);
        } catch (\Throwable) {
            return null;
        }

        $data = json_decode($decrypted, true);

        if (! is_array($data) || ! isset($data['pwd'], $data['kid'], $data['ts'])) {
            return null;
        }

        return $data;
    }

    public function getCurrentKey(): array
    {
        return Cache::remember('rsa_current_key', 300, function () {
            $keys = $this->generateKeyPair();

            return [
                'key_id' => $keys['key_id'],
                'public_key' => $keys['public_key'],
                'expires_at' => now()->addMinutes(5)->toIso8601String(),
            ];
        });
    }

    public function cleanupOldKeys(int $maxAgeMinutes = 30): void
    {
        if (! is_dir($this->keysPath)) {
            return;
        }

        $now = time();

        foreach (glob("{$this->keysPath}/*") as $file) {
            if ($now - filemtime($file) > $maxAgeMinutes * 60) {
                @unlink($file);
            }
        }
    }
}
