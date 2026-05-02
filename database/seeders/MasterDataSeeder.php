<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = [
            // Posts (Berita)
            ['module_type' => 'post', 'name' => 'Pembangunan', 'slug' => 'pembangunan', 'description' => 'Berita seputar pembangunan infrastruktur dan fasilitas desa'],
            ['module_type' => 'post', 'name' => 'Pendidikan', 'slug' => 'pendidikan', 'description' => 'Program dan kegiatan pendidikan di desa'],
            ['module_type' => 'post', 'name' => 'Kesehatan', 'slug' => 'kesehatan', 'description' => 'Kegiatan kesehatan masyarakat dan posyandu'],
            ['module_type' => 'post', 'name' => 'Pertanian', 'slug' => 'pertanian', 'description' => 'Informasi sektor pertanian dan perkebunan'],
            ['module_type' => 'post', 'name' => 'Ekonomi', 'slug' => 'ekonomi', 'description' => 'Pemberdayaan ekonomi masyarakat dan UMKM'],
            ['module_type' => 'post', 'name' => 'Sosial', 'slug' => 'sosial', 'description' => 'Kegiatan sosial dan kemasyarakatan'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Tags
        $tags = [
            'Pembangunan',
            'Infrastruktur',
            'Pendidikan',
            'Kesehatan',
            'Pertanian',
            'Ekonomi',
            'Dana Desa',
            'BUMDes',
            'Gotong Royong',
            'Transparansi',
            'Pemberdayaan',
            'Beasiswa',
            'Jalan Desa',
            'Irigasi',
            'Posyandu',
        ];

        foreach ($tags as $tagName) {
            Tag::create([
                'name' => $tagName,
                'slug' => \Illuminate\Support\Str::slug($tagName),
            ]);
        }
    }
}
