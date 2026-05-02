<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Services\RsaEncryptionService;
use Illuminate\Http\JsonResponse;

class PublicKeyController
{
    public function __construct(
        private readonly RsaEncryptionService $rsaService
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $key = $this->rsaService->getCurrentKey();

        return response()->json([
            'public_key' => $key['public_key'],
            'key_id' => $key['key_id'],
            'expires_at' => $key['expires_at'],
        ]);
    }
}
