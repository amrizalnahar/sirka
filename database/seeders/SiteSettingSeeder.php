<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'Kita Makmur Bersama',
            'village_name' => 'Kita',
            'site_description' => 'Website resmi calon Kepala Desa Kita Periode 2026-2034. Wujudkan desa maju, mandiri, dan sejahtera bersama masyarakat.',
            'contact_email' => null,
            'contact_phone' => null,
            'contact_address' => 'Jl. Dworowati Dusun I RT 02 RW 01',
            'mail_from_address' => 'admin@desa-kita.id',
            'social_facebook' => 'https://facebook.com/',
            'social_instagram' => 'https://instagram.com/',
            'social_whatsapp' => null,
            'social_tiktok' => null,
            'social_twitter' => null,
            'social_youtube' => null,
            'village_region' => 'Indonesia',
            'org_type' => 'Individu',
            'seo_site_name' => 'Kita Makmur Bersama',
            'seo_description' => 'Website resmi calon Kepala Desa Kita Periode 2026-2034. Wujudkan desa maju, mandiri, dan sejahtera bersama masyarakat.',
            'seo_author' => 'Tim Kampanye Desa Kita Makmur',
            'ga4_measurement_id' => null,
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::setValue($key, $value);
        }
    }
}
