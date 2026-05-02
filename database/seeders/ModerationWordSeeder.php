<?php

namespace Database\Seeders;

use App\Models\ModerationWord;
use Illuminate\Database\Seeder;

class ModerationWordSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            // === VULGAR (high = auto-block) ===
            ['word' => 'anjing', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'babi', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'kampret', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'kontol', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'memek', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'ngentot', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'ngewe', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'pepek', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'tempek', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'coli', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'colay', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'jancuk', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'jembud', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'perek', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'bego', 'category' => 'vulgar', 'severity' => 'medium'],
            ['word' => 'goblok', 'category' => 'vulgar', 'severity' => 'medium'],
            ['word' => 'tolol', 'category' => 'vulgar', 'severity' => 'medium'],
            ['word' => 'idiot', 'category' => 'vulgar', 'severity' => 'medium'],
            ['word' => 'bangsat', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'brengsek', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'tai', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'tae', 'category' => 'vulgar', 'severity' => 'high'],
            ['word' => 'setan', 'category' => 'vulgar', 'severity' => 'medium'],
            ['word' => 'iblis', 'category' => 'vulgar', 'severity' => 'medium'],

            // === SARA (high = auto-block) ===
            ['word' => 'cina babi', 'category' => 'sara', 'severity' => 'high'],
            ['word' => 'arab jawa', 'category' => 'sara', 'severity' => 'high'],
            ['word' => 'kafir', 'category' => 'sara', 'severity' => 'high'],
            ['word' => 'nasrani hina', 'category' => 'sara', 'severity' => 'high'],
            ['word' => 'islam radikal', 'category' => 'sara', 'severity' => 'high'],
            ['word' => ' Kristen sesat', 'category' => 'sara', 'severity' => 'high'],
            ['word' => 'Hindu hina', 'category' => 'sara', 'severity' => 'high'],
            ['word' => 'Budha hina', 'category' => 'sara', 'severity' => 'high'],
            ['word' => ' Yahudi', 'category' => 'sara', 'severity' => 'medium'],
            ['word' => 'pribumi', 'category' => 'sara', 'severity' => 'medium'],
            ['word' => 'asing', 'category' => 'sara', 'severity' => 'low'],
            ['word' => ' pendatang', 'category' => 'sara', 'severity' => 'low'],

            // === HATE SPEECH (high = auto-block) ===
            ['word' => 'bunuh', 'category' => 'hate_speech', 'severity' => 'high'],
            ['word' => 'matiin', 'category' => 'hate_speech', 'severity' => 'high'],
            ['word' => 'habisin', 'category' => 'hate_speech', 'severity' => 'high'],
            ['word' => 'musnahkan', 'category' => 'hate_speech', 'severity' => 'high'],
            ['word' => 'perang saudara', 'category' => 'hate_speech', 'severity' => 'high'],
            ['word' => 'tumpas', 'category' => 'hate_speech', 'severity' => 'high'],
            ['word' => 'ancam', 'category' => 'hate_speech', 'severity' => 'medium'],
            ['word' => 'bakar', 'category' => 'hate_speech', 'severity' => 'medium'],
            ['word' => 'hancurkan', 'category' => 'hate_speech', 'severity' => 'medium'],
            ['word' => 'rampas', 'category' => 'hate_speech', 'severity' => 'medium'],

            // === SPAM (medium = flag, not block) ===
            // 3+ URLs dalam satu pesan
            ['word' => '/(https?:\/\/\S+).*(https?:\/\/\S+).*(https?:\/\/\S+)/i', 'category' => 'spam', 'severity' => 'medium', 'is_regex' => true],
            // Link shortener / referral
            ['word' => '/\b(whatsapp|wa\.me|bit\.ly|shortlink|referral|shopee\.ee|tokopedia\.link|s\.click)\b/i', 'category' => 'spam', 'severity' => 'medium', 'is_regex' => true],
            // Nomor KTP (16 digit berurutan)
            ['word' => '/\b\d{16}\b/', 'category' => 'spam', 'severity' => 'medium', 'is_regex' => true],
            // Nomor HP Indonesia
            ['word' => '/\b(0\d{9,11}|\+62\d{9,11}|62\d{9,11})\b/', 'category' => 'spam', 'severity' => 'medium', 'is_regex' => true],
            // Email address
            ['word' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', 'category' => 'spam', 'severity' => 'low', 'is_regex' => true],
            // Tawa berlebihan (wkwkwk, hahaha, dll diulang >4x)
            ['word' => '/\b(ha|wk|he|hi|ho){4,}\b/i', 'category' => 'spam', 'severity' => 'low', 'is_regex' => true],
            // CAPS LOCK berlebihan (>15 huruf kapital berurutan)
            ['word' => '/[A-Z\s]{15,}/', 'category' => 'spam', 'severity' => 'low', 'is_regex' => true],
            // Tanda bantu berulang (>3x sama)
            ['word' => '/([!?.])\1{3,}/', 'category' => 'spam', 'severity' => 'low', 'is_regex' => true],
            // Nomor rekening bank (10-16 digit)
            ['word' => '/\b\d{10,16}\b/', 'category' => 'spam', 'severity' => 'medium', 'is_regex' => true],
        ];

        foreach ($words as $word) {
            ModerationWord::firstOrCreate(
                ['word' => $word['word'], 'category' => $word['category']],
                [
                    'severity' => $word['severity'],
                    'is_regex' => $word['is_regex'] ?? false,
                    'is_active' => true,
                ]
            );
        }
    }
}
