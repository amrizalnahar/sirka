<?php

declare(strict_types=1);

namespace App\Helpers;

class Terbilang
{
    public static function convert(int $number): string
    {
        $number = abs($number);
        $angka = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return $angka[$number] ?: 'nol';
        }
        if ($number < 20) {
            return $angka[$number - 10] . ' belas';
        }
        if ($number < 100) {
            $hasil = $angka[(int) ($number / 10)] . ' puluh';
            if ($number % 10 !== 0) {
                $hasil .= ' ' . $angka[$number % 10];
            }
            return $hasil;
        }
        if ($number < 200) {
            $hasil = 'seratus';
            if ($number !== 100) {
                $hasil .= ' ' . self::convert($number - 100);
            }
            return $hasil;
        }
        if ($number < 1000) {
            $hasil = $angka[(int) ($number / 100)] . ' ratus';
            if ($number % 100 !== 0) {
                $hasil .= ' ' . self::convert($number % 100);
            }
            return $hasil;
        }
        if ($number < 2000) {
            $hasil = 'seribu';
            if ($number !== 1000) {
                $hasil .= ' ' . self::convert($number - 1000);
            }
            return $hasil;
        }
        if ($number < 1000000) {
            $hasil = self::convert((int) ($number / 1000)) . ' ribu';
            if ($number % 1000 !== 0) {
                $hasil .= ' ' . self::convert($number % 1000);
            }
            return $hasil;
        }
        if ($number < 1000000000) {
            $hasil = self::convert((int) ($number / 1000000)) . ' juta';
            if ($number % 1000000 !== 0) {
                $hasil .= ' ' . self::convert($number % 1000000);
            }
            return $hasil;
        }
        if ($number < 1000000000000) {
            $hasil = self::convert((int) ($number / 1000000000)) . ' miliar';
            if ($number % 1000000000 !== 0) {
                $hasil .= ' ' . self::convert($number % 1000000000);
            }
            return $hasil;
        }

        return 'angka terlalu besar';
    }
}
