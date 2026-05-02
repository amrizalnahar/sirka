<?php

namespace App\Helpers;

use App\Models\ModerationWord;

class ContentModerator
{
    /**
     * Cache daftar kata dari database.
     */
    private static ?array $wordCache = null;

    /**
     * Normalisasi teks untuk anti-bypass.
     */
    public static function normalize(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/\s+/', '', $text);
        $text = preg_replace('/[^a-z0-9]/', '', $text);

        $replacements = [
            '4' => 'a', '@' => 'a',
            '0' => 'o',
            '3' => 'e',
            '1' => 'i', '!' => 'i',
            '5' => 's', '$' => 's',
            '7' => 't',
            '8' => 'b',
            '9' => 'g',
        ];

        $text = strtr($text, $replacements);
        $text = preg_replace('/(.)\1{2,}/', '$1$1', $text);

        return $text;
    }

    /**
     * Scan teks dan kembalikan hasil moderasi.
     */
    public static function scan(string $text): array
    {
        $normalized = self::normalize($text);
        $violations = [];

        $words = self::getActiveWords();

        foreach ($words as $word) {
            if (! $word['is_active']) {
                continue;
            }

            if ($word['is_regex']) {
                if (preg_match($word['word'], $text)) {
                    $violations[] = [
                        'category' => $word['category'],
                        'severity' => $word['severity'],
                        'matched' => $word['word'],
                    ];
                }
                continue;
            }

            $normalizedWord = self::normalize($word['word']);

            if (str_contains($normalized, $normalizedWord)) {
                $violations[] = [
                    'category' => $word['category'],
                    'severity' => $word['severity'],
                    'matched' => $word['word'],
                ];
            }
        }

        if (empty($violations)) {
            return ['blocked' => false, 'flagged' => false];
        }

        $hasBlock = collect($violations)->contains(
            fn ($v) => in_array($v['category'], ['vulgar', 'sara', 'hate_speech'])
                || $v['severity'] === 'high'
        );

        return [
            'blocked' => $hasBlock,
            'flagged' => ! $hasBlock,
            'violations' => $violations,
        ];
    }

    /**
     * Ambil daftar kata aktif dari database (dengan cache per-request).
     */
    private static function getActiveWords(): array
    {
        if (self::$wordCache !== null) {
            return self::$wordCache;
        }

        self::$wordCache = ModerationWord::active()->get()->toArray();

        return self::$wordCache;
    }

    /**
     * Clear cache (berguna setelah admin mengubah daftar kata).
     */
    public static function clearCache(): void
    {
        self::$wordCache = null;
    }
}
