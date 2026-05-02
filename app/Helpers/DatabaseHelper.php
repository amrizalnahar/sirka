<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DatabaseHelper
{
    /**
     * Return a database-agnostic raw expression for extracting the year from a date column.
     */
    public static function year(string $column): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y', {$column})",
            default => "YEAR({$column})",
        };
    }

    /**
     * Return a database-agnostic raw expression for extracting the month from a date column.
     */
    public static function month(string $column): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "strftime('%m', {$column})",
            default => "MONTH({$column})",
        };
    }

    /**
     * Return a database-agnostic raw expression for extracting the day from a date column.
     */
    public static function day(string $column): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "strftime('%d', {$column})",
            default => "DAY({$column})",
        };
    }

    /**
     * Return a database-agnostic raw expression for formatting a date column.
     */
    public static function dateFormat(string $column, string $format): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "strftime('{$format}', {$column})",
            default => "DATE_FORMAT({$column}, '{$format}')",
        };
    }
}
