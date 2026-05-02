<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\RsaEncryptionService;
use Closure;
use Illuminate\Http\Request;

class DecryptPasswordMiddleware
{
    public function __construct(
        private readonly RsaEncryptionService $rsaService
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $keyId = $this->extractKeyId($request);

        if (! $keyId) {
            return $next($request);
        }

        if ($this->isLivewireRequest($request)) {
            $this->decryptLivewireRequest($request, $keyId);
        } else {
            $this->decryptStandardRequest($request, $keyId);
        }

        return $next($request);
    }

    private function extractKeyId(Request $request): ?string
    {
        // Standard form field
        $keyId = $request->input('encryption_key_id');
        if (is_string($keyId) && $keyId !== '') {
            return $keyId;
        }

        // Livewire: check in JSON body updates
        if ($this->isLivewireRequest($request)) {
            $components = $request->input('components', []);
            foreach ($components as $component) {
                $updates = $component['updates'] ?? [];
                foreach (['encryption_key_id', 'encryptionKeyId'] as $key) {
                    if (isset($updates[$key]) && is_string($updates[$key]) && $updates[$key] !== '') {
                        return $updates[$key];
                    }
                }
            }
        }

        return null;
    }

    private function isLivewireRequest(Request $request): bool
    {
        return $request->isJson()
            || str_contains($request->path(), 'livewire')
            || $request->hasHeader('X-Livewire');
    }

    private function decryptStandardRequest(Request $request, string $keyId): void
    {
        foreach (['password', 'password_confirmation'] as $field) {
            $encrypted = $request->input($field);

            if (! is_string($encrypted) || $encrypted === '') {
                continue;
            }

            $plain = $this->tryDecrypt($encrypted, $keyId);

            if ($plain !== null) {
                $request->merge([$field => $plain]);
            }
        }
    }

    private function decryptLivewireRequest(Request $request, string $keyId): void
    {
        $components = $request->input('components', []);
        $modified = false;

        foreach ($components as &$component) {
            if (! isset($component['updates']) || ! is_array($component['updates'])) {
                continue;
            }

            foreach (['form.password', 'password', 'form.password_confirmation', 'password_confirmation'] as $updateKey) {
                if (! isset($component['updates'][$updateKey])) {
                    continue;
                }

                $encrypted = $component['updates'][$updateKey];
                if (! is_string($encrypted) || $encrypted === '') {
                    continue;
                }

                $plain = $this->tryDecrypt($encrypted, $keyId);

                if ($plain !== null) {
                    $component['updates'][$updateKey] = $plain;
                    $modified = true;
                }
            }
        }
        unset($component);

        if ($modified) {
            $request->merge(['components' => $components]);
        }
    }

    private function tryDecrypt(string $encrypted, string $keyId): ?string
    {
        $data = $this->rsaService->decrypt($encrypted, $keyId);

        if ($data === null) {
            return null;
        }

        // Anti-replay: timestamp must be within 60 seconds
        if (! isset($data['ts']) || abs(now()->timestamp - (int) $data['ts']) > 60) {
            return null;
        }

        // Validate key_id matches
        if (! isset($data['kid']) || $data['kid'] !== $keyId) {
            return null;
        }

        return $data['pwd'];
    }
}
