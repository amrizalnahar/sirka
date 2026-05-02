<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::role('super-admin')->first();
        $editorUser = User::role('editor')->first();
        $authorId = $adminUser?->id ?? $editorUser?->id ?? 1;

        $postCategories = Category::byModule('post')->get();
        $allTags = Tag::all();

        // Posts (Berita) - 15 items
        $posts = [
            [
                'title' => 'Pembangunan Jalan Baru Dusun I Resmi Dimulai',
                'content' => 'Alhamdulillah, pembangunan jalan beton sepanjang 1,2 kilometer di Dusun I Desa Sumber Makmur resmi dimulai hari ini. Proyek ini merupakan hasil kerja sama antara dana desa dan swadaya masyarakat.Kegiatan diawali dengan peletakan batu pertama oleh Ketua BPD dan perwakilan warga. Diharapkan dengan adanya jalan ini, aktivitas warga dan distribusi hasil pertanian dapat lebih lancar.',
                'category' => 'Pembangunan',
                'tags' => ['Pembangunan', 'Infrastruktur', 'Jalan Desa'],
                'published_at' => '2024-03-15 08:00:00',
            ],
            [
                'title' => 'Pemberian Beasiswa untuk 50 Siswa Berprestasi',
                'content' => 'Dalam rangka meningkatkan mutu pendidikan di Desa Sumber Makmur, kami menyalurkan beasiswa untuk 50 siswa berprestasi dari SD hingga SMA. Beasiswa ini berasal dari dana BUMDes Sumber Makmur Sejahtera.Kriteria seleksi meliputi nilai akademik, aktivitas organisasi, dan kondisi ekonomi keluarga. Kami percaya investasi terbaik untuk masa depan desa adalah pendidikan anak-anak kita.',
                'category' => 'Pendidikan',
                'tags' => ['Pendidikan', 'Beasiswa', 'Pemberdayaan'],
                'published_at' => '2024-04-22 10:00:00',
            ],
            [
                'title' => 'Posyandu Balita dan Lansia Raih Predikat Terbaik Kecamatan',
                'content' => 'Posyandu Melati di Dusun II berhasil meraih predikat Posyandu Terbaik se-Kecamatan Sejahtera tahun 2024. Prestasi ini diraih berkat kerja keras kader posyandu dan dukungan penuh dari warga.Kegiatan posyandu rutin dilaksanakan setiap bulan dengan cakupan imunisasi, pemantauan gizi, dan penyuluhan kesehatan. Kami berkomitmen untuk memperluas layanan ini ke seluruh dusun.',
                'category' => 'Kesehatan',
                'tags' => ['Kesehatan', 'Posyandu'],
                'published_at' => '2024-05-10 09:00:00',
            ],
            [
                'title' => 'Petani Sumber Makmur Panen Raya Padi Organik',
                'content' => 'Ratusan petani di Desa Sumber Makmur menggelar panen raya padi organik hasil program pertanian ramah lingkungan. Hasil panen tahun ini meningkat 25% dibandingkan tahun lalu.Program ini didukung oleh Dinas Pertanian Kabupaten dengan pendampingan teknis dan bantuan pupuk organik. Kami mendorong petani untuk beralih ke pertanian organik demi kelestarian tanah dan kesehatan konsumen.',
                'category' => 'Pertanian',
                'tags' => ['Pertanian', 'Ekonomi', 'Lingkungan'],
                'published_at' => '2024-06-18 07:00:00',
            ],
            [
                'title' => 'BUMDes Sumber Makmur Sejahtera Luncurkan Toko Online',
                'content' => 'BUMDes Sumber Makmur Sejahtera meluncurkan platform digital untuk memasarkan produk unggulan desa. Melalui toko online ini, produk seperti kerajinan bambu, madu hutan, dan olahan pertanian dapat dipasarkan hingga ke luar pulau.Inovasi ini diharapkan dapat meningkatkan omzet UMKM desa dan membuka lapangan kerja baru bagi generasi muda.',
                'category' => 'Ekonomi',
                'tags' => ['Ekonomi', 'BUMDes', 'UMKM'],
                'published_at' => '2024-07-05 14:00:00',
            ],
            [
                'title' => 'Gotong Royong Perbaiki Talud Sungai Desa',
                'content' => 'Ratusan warga Desa Sumber Makmur bergotong royong memperbaiki talud sungai yang longsor akibat hujan deras. Kegiatan ini berlangsung selama tiga hari dengan melibatkan warga dari semua dusun.Perbaikan talud ini penting untuk melindungi lahan pertanian dan rumah warga dari ancaman banjir. Kami berterima kasih atas semangat kebersamaan masyarakat.',
                'category' => 'Sosial',
                'tags' => ['Sosial', 'Gotong Royong', 'Infrastruktur'],
                'published_at' => '2024-08-12 06:00:00',
            ],
            [
                'title' => 'Desa Sumber Makmur Terima Penghargaan Desa Sehat',
                'content' => 'Alhamdulillah, Desa Sumber Makmur menerima penghargaan Desa Sehat tingkat Kabupaten. Penghargaan ini diberikan atas komitmen desa dalam menyediakan sanitasi layak, air bersih, dan pola hidup sehat bagi warga.Kami berterima kasih kepada semua pihak yang telah mendukung program ini. Semoga prestasi ini menjadi motivasi untuk terus meningkatkan kualitas hidup masyarakat.',
                'category' => 'Kesehatan',
                'tags' => ['Kesehatan', 'Pembangunan'],
                'published_at' => '2024-09-20 11:00:00',
            ],
            [
                'title' => 'Pelatihan Kerajinan Bambu untuk Ibu-Ibu PKK',
                'content' => 'Puluhan ibu-ibu PKK mengikuti pelatihan kerajinan bambu yang diselenggarakan oleh Dinas Perdagangan dan UMKM Kabupaten. Peserta diajarkan teknik anyaman bambu modern yang bernilai ekonomi tinggi.Hasil kerajinan akan dipasarkan melalui BUMDes dan pameran-pameran lokal. Program ini bertujuan meningkatkan kemandirian ekonomi keluarga melalui usaha rumahan.',
                'category' => 'Ekonomi',
                'tags' => ['Ekonomi', 'UMKM', 'Pemberdayaan'],
                'published_at' => '2024-10-08 09:30:00',
            ],
            [
                'title' => 'Pembangunan Taman Bacaan Perpustakaan Desa',
                'content' => 'Taman bacaan perpustakaan desa mulai dibangun di lahan seluas 200 meter persegi. Fasilitas ini akan dilengkapi dengan koleksi buku pelajaran, ensiklopedia, dan literatur umum.Kami juga mengajak warga untuk berdonasi buku bekas yang masih layak baca. Taman bacaan ini diharapkan menjadi pusat literasi dan ruang belajar anak-anak desa.',
                'category' => 'Pendidikan',
                'tags' => ['Pendidikan', 'Pembangunan'],
                'published_at' => '2024-11-15 08:00:00',
            ],
            [
                'title' => 'Vaksinasi Hewan Ternak Massal Berhasil Dilaksanakan',
                'content' => 'Dinas Peternakan Kabupaten bersama Puskeswan melaksanakan vaksinasi hewan ternak massal di Desa Sumber Makmur. Sebanyak 350 ekor sapi dan 500 ekor kambing telah divaksinasi secara gratis.Kegiatan ini bertujuan mencegah penyebaran penyakit mulut dan kuku (PMK) serta meningkatkan kesehatan ternak warga.',
                'category' => 'Pertanian',
                'tags' => ['Pertanian', 'Kesehatan'],
                'published_at' => '2024-12-05 07:30:00',
            ],
            [
                'title' => 'Penerangan Jalan Umum (PJU) Tenaga Surya Terpasang',
                'content' => 'Sebanyak 30 unit lampu jalan tenaga surya (PJU) telah berhasil dipasang di sepanjang jalan utama desa. Proyek ini merupakan kerja sama dengan Dinas ESDM Kabupaten dan PT Hijau Lestari.PJU tenaga surya dipilih karena ramah lingkungan dan hemat biaya operasional. Warga sangat antusias karena kini aktivitas malam hari menjadi lebih aman dan nyaman.',
                'category' => 'Infrastruktur',
                'tags' => ['Infrastruktur', 'Pembangunan', 'Lingkungan'],
                'published_at' => '2025-01-20 18:00:00',
            ],
            [
                'title' => 'Pelatihan Kewirausahaan untuk Pemuda Desa',
                'content' => 'Puluhan pemuda Desa Sumber Makmur mengikuti pelatihan kewirausahaan yang diselenggarakan oleh Dinas Koperasi dan UMKM. Materi meliputi manajemen usaha, pemasaran digital, dan akses permodalan.Banyak peserta yang langsung mempraktikkan ilmu dengan membuka usaha kecil-kecilan. Kami berharap pelatihan ini menjadi awal lahirnya pengusaha-pengusaha muda di desa kita.',
                'category' => 'Ekonomi',
                'tags' => ['Ekonomi', 'Pemberdayaan', 'Kepemudaan'],
                'published_at' => '2025-02-14 09:00:00',
            ],
            [
                'title' => 'Pembangunan Jembatan Gantung Penghubung Dusun IV',
                'content' => 'Jembatan gantung penghubung Dusun IV dengan pusat desa telah selesai dibangun. Jembatan ini menggantikan jembatan kayu tua yang sudah tidak layak pakai dan berbahaya.Proyek ini dikerjakan oleh warga setempat dengan pendampingan teknis dari Dinas Pekerjaan Umum. Kini warga Dusun IV bisa mengakses pelayanan desa dengan lebih mudah dan aman.',
                'category' => 'Infrastruktur',
                'tags' => ['Infrastruktur', 'Pembangunan', 'Jembatan'],
                'published_at' => '2025-03-10 07:00:00',
            ],
            [
                'title' => 'Desa Sumber Makmur Jadi Percontohan Desa Wisata',
                'content' => 'Desa Sumber Makmur terpilih sebagai desa percontohan wisata budaya dan alam oleh Dinas Pariwisata Kabupaten. Program ini akan mendampingi desa dalam pengembangan atraksi wisata, pelatihan SDM, dan pemasaran.Potensi wisata desa kita sangat kaya: sawah terasering, air terjun, kampung adat, dan kuliner tradisional. Kami berharap wisata bisa menjadi sumber pendapatan baru bagi warga.',
                'category' => 'Pariwisata',
                'tags' => ['Pariwisata', 'Ekonomi', 'Pembangunan'],
                'published_at' => '2025-04-22 08:00:00',
            ],
            [
                'title' => 'Peringatan HUT RI ke-79 di Lapangan Desa',
                'content' => 'Warga Desa Sumber Makmur merayakan HUT Republik Indonesia ke-79 dengan berbagai lomba dan kegiatan gotong royong. Acara diawali dengan upacara bendera yang diikuti oleh perwakilan dari setiap dusun.Lomba yang diadakan meliputi panjat pinang, balap karung, makan kerupuk, dan lomba tujuh belasan antar-RT. Semangat kebersamaan terlihat dari antusiasme warga yang ikut berpartisipasi.',
                'category' => 'Sosial',
                'tags' => ['Sosial', 'Pendidikan'],
                'published_at' => '2026-07-05 15:00:00',
            ],
        ];

        foreach ($posts as $postData) {
            $category = $postCategories->firstWhere('name', $postData['category']);
            $post = Post::create([
                'title' => $postData['title'],
                'slug' => Str::slug($postData['title']),
                'content' => $postData['content'],
                'category_id' => $category?->id,
                'status' => 'published',
                'published_at' => $postData['published_at'],
                'author_id' => $authorId,
            ]);

            $tagIds = $allTags->whereIn('name', $postData['tags'])->pluck('id')->toArray();
            $post->tags()->attach($tagIds);
        }
    }
}
