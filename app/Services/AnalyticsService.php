<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get multi-metric statistics based on period type and filters.
     *
     * @return array<string, array<int, int>>
     */
    public function getMultiMetricStats(string $periodType, ?int $year = null, ?int $month = null): array
    {
        $year ??= now()->year;
        $month ??= now()->month;

        return match ($periodType) {
            'yearly' => $this->getYearlyStats(),
            'semester' => $this->getSemesterStats($year),
            'quarterly' => $this->getQuarterlyStats($year),
            'monthly' => $this->getMonthlyStats($year),
            'weekly' => $this->getWeeklyStats($year, $month),
            'daily' => $this->getDailyStats($year, $month),
            default => $this->getMonthlyStats($year),
        };
    }

    /**
     * Get report amount statistics based on period type.
     *
     * @return array<string, mixed>
     */
    public function getReportAmountStats(string $periodType, ?int $year = null, ?int $month = null): array
    {
        $year ??= now()->year;
        $month ??= now()->month;

        return match ($periodType) {
            'yearly' => $this->getYearlyAmountStats(),
            'semester' => $this->getSemesterAmountStats($year),
            'quarterly' => $this->getQuarterlyAmountStats($year),
            'monthly' => $this->getMonthlyAmountStats($year),
            default => $this->getMonthlyAmountStats($year),
        };
    }

    /* ─────────────────────────── Yearly ─────────────────────────── */

    private function getYearlyStats(): array
    {
        $years = range(now()->year - 4, now()->year);
        $labels = array_map(fn (int $y) => (string) $y, $years);

        return [
            'labels' => $labels,
            'visits' => $this->countByYear('visits', 'visited_at', $years),
            'posts' => $this->countPublishedByYear('posts', $years),
            'notes' => $this->countPublishedByYear('notes', $years),
            'aspirations' => $this->countByYear('aspirations', 'created_at', $years),
            'reports' => $this->countPublishedByYear('reports', $years),
        ];
    }

    private function getYearlyAmountStats(): array
    {
        $years = range(now()->year - 4, now()->year);
        $labels = array_map(fn (int $y) => (string) $y, $years);
        $amounts = [];

        foreach ($years as $y) {
            $amounts[] = (float) DB::table('reports')
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->whereYear('activity_date', $y)
                ->sum('amount');
        }

        return $this->buildAmountResponse($labels, $amounts);
    }

    /* ─────────────────────────── Semester ─────────────────────────── */

    private function getSemesterStats(int $year): array
    {
        $labels = ['Semester 1', 'Semester 2'];

        return [
            'labels' => $labels,
            'visits' => [
                $this->countInRange('visits', 'visited_at', "{$year}-01-01", "{$year}-06-30 23:59:59"),
                $this->countInRange('visits', 'visited_at', "{$year}-07-01", "{$year}-12-31 23:59:59"),
            ],
            'posts' => [
                $this->countPublishedInRange('posts', "{$year}-01-01", "{$year}-06-30 23:59:59"),
                $this->countPublishedInRange('posts', "{$year}-07-01", "{$year}-12-31 23:59:59"),
            ],
            'notes' => [
                $this->countPublishedInRange('notes', "{$year}-01-01", "{$year}-06-30 23:59:59"),
                $this->countPublishedInRange('notes', "{$year}-07-01", "{$year}-12-31 23:59:59"),
            ],
            'aspirations' => [
                $this->countInRange('aspirations', 'created_at', "{$year}-01-01", "{$year}-06-30 23:59:59"),
                $this->countInRange('aspirations', 'created_at', "{$year}-07-01", "{$year}-12-31 23:59:59"),
            ],
            'reports' => [
                $this->countPublishedInRange('reports', "{$year}-01-01", "{$year}-06-30 23:59:59"),
                $this->countPublishedInRange('reports', "{$year}-07-01", "{$year}-12-31 23:59:59"),
            ],
        ];
    }

    private function getSemesterAmountStats(int $year): array
    {
        $labels = ['Semester 1', 'Semester 2'];
        $amounts = [
            (float) DB::table('reports')->where('status', 'published')->whereNull('deleted_at')->whereBetween('activity_date', ["{$year}-01-01", "{$year}-06-30"])->sum('amount'),
            (float) DB::table('reports')->where('status', 'published')->whereNull('deleted_at')->whereBetween('activity_date', ["{$year}-07-01", "{$year}-12-31"])->sum('amount'),
        ];

        return $this->buildAmountResponse($labels, $amounts);
    }

    /* ─────────────────────────── Quarterly ─────────────────────────── */

    private function getQuarterlyStats(int $year): array
    {
        $ranges = [
            ['start' => "{$year}-01-01", 'end' => "{$year}-03-31 23:59:59", 'label' => 'Q1'],
            ['start' => "{$year}-04-01", 'end' => "{$year}-06-30 23:59:59", 'label' => 'Q2'],
            ['start' => "{$year}-07-01", 'end' => "{$year}-09-30 23:59:59", 'label' => 'Q3'],
            ['start' => "{$year}-10-01", 'end' => "{$year}-12-31 23:59:59", 'label' => 'Q4'],
        ];

        $labels = array_column($ranges, 'label');

        return [
            'labels' => $labels,
            'visits' => array_map(fn ($r) => $this->countInRange('visits', 'visited_at', $r['start'], $r['end']), $ranges),
            'posts' => array_map(fn ($r) => $this->countPublishedInRange('posts', $r['start'], $r['end']), $ranges),
            'notes' => array_map(fn ($r) => $this->countPublishedInRange('notes', $r['start'], $r['end']), $ranges),
            'aspirations' => array_map(fn ($r) => $this->countInRange('aspirations', 'created_at', $r['start'], $r['end']), $ranges),
            'reports' => array_map(fn ($r) => $this->countPublishedInRange('reports', $r['start'], $r['end']), $ranges),
        ];
    }

    private function getQuarterlyAmountStats(int $year): array
    {
        $ranges = [
            ["{$year}-01-01", "{$year}-03-31"],
            ["{$year}-04-01", "{$year}-06-30"],
            ["{$year}-07-01", "{$year}-09-30"],
            ["{$year}-10-01", "{$year}-12-31"],
        ];
        $labels = ['Q1', 'Q2', 'Q3', 'Q4'];
        $amounts = array_map(fn ($r) => (float) DB::table('reports')->where('status', 'published')->whereNull('deleted_at')->whereBetween('activity_date', $r)->sum('amount'), $ranges);

        return $this->buildAmountResponse($labels, $amounts);
    }

    /* ─────────────────────────── Monthly ─────────────────────────── */

    private function getMonthlyStats(int $year): array
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return [
            'labels' => $labels,
            'visits' => $this->countByMonth('visits', 'visited_at', $year),
            'posts' => $this->countPublishedByMonth('posts', $year),
            'notes' => $this->countPublishedByMonth('notes', $year),
            'aspirations' => $this->countByMonth('aspirations', 'created_at', $year),
            'reports' => $this->countPublishedByMonth('reports', $year),
        ];
    }

    private function getMonthlyAmountStats(int $year): array
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $amounts = [];

        for ($m = 1; $m <= 12; $m++) {
            $amounts[] = (float) DB::table('reports')
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->whereYear('activity_date', $year)
                ->whereMonth('activity_date', $m)
                ->sum('amount');
        }

        return $this->buildAmountResponse($labels, $amounts);
    }

    /* ─────────────────────────── Weekly ─────────────────────────── */

    private function getWeeklyStats(int $year, int $month): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $weeks = [];
        $current = $startOfMonth->copy()->startOfWeek();
        while ($current <= $endOfMonth) {
            $weekEnd = $current->copy()->endOfWeek();
            $weeks[] = [
                'start' => $current->copy(),
                'end' => $weekEnd->copy(),
                'label' => 'M' . count($weeks) + 1,
            ];
            $current->addWeek();
        }

        $labels = array_column($weeks, 'label');

        return [
            'labels' => $labels,
            'visits' => array_map(fn ($w) => $this->countInRange('visits', 'visited_at', $w['start']->toDateTimeString(), $w['end']->toDateTimeString()), $weeks),
            'posts' => array_map(fn ($w) => $this->countPublishedInRange('posts', $w['start']->toDateString(), $w['end']->toDateTimeString()), $weeks),
            'notes' => array_map(fn ($w) => $this->countPublishedInRange('notes', $w['start']->toDateString(), $w['end']->toDateTimeString()), $weeks),
            'aspirations' => array_map(fn ($w) => $this->countInRange('aspirations', 'created_at', $w['start']->toDateTimeString(), $w['end']->toDateTimeString()), $weeks),
            'reports' => array_map(fn ($w) => $this->countPublishedInRange('reports', $w['start']->toDateString(), $w['end']->toDateTimeString()), $weeks),
        ];
    }

    /* ─────────────────────────── Daily ─────────────────────────── */

    private function getDailyStats(int $year, int $month): array
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $labels = [];
        $visits = [];
        $posts = [];
        $notes = [];
        $aspirations = [];
        $reports = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $labels[] = (string) $d;

            $visits[] = DB::table('visits')->whereDate('visited_at', $date)->count();
            $posts[] = DB::table('posts')->where('status', 'published')->whereNull('deleted_at')->whereDate('published_at', $date)->count();
            $notes[] = DB::table('notes')->where('status', 'published')->whereNull('deleted_at')->whereDate('published_at', $date)->count();
            $aspirations[] = DB::table('aspirations')->whereNull('deleted_at')->whereDate('created_at', $date)->count();
            $reports[] = DB::table('reports')->where('status', 'published')->whereNull('deleted_at')->whereDate('activity_date', $date)->count();
        }

        return [
            'labels' => $labels,
            'visits' => $visits,
            'posts' => $posts,
            'notes' => $notes,
            'aspirations' => $aspirations,
            'reports' => $reports,
        ];
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    private function countByYear(string $table, string $column, array $years): array
    {
        return array_map(fn (int $y) => DB::table($table)->when($table !== 'visits', fn ($q) => $q->whereNull('deleted_at'))->whereYear($column, $y)->count(), $years);
    }

    private function countPublishedByYear(string $table, array $years): array
    {
        return array_map(fn (int $y) => DB::table($table)->where('status', 'published')->whereNull('deleted_at')->whereYear('published_at', $y)->count(), $years);
    }

    private function countByMonth(string $table, string $column, int $year): array
    {
        $counts = [];
        for ($m = 1; $m <= 12; $m++) {
            $counts[] = DB::table($table)->when($table !== 'visits', fn ($q) => $q->whereNull('deleted_at'))->whereYear($column, $year)->whereMonth($column, $m)->count();
        }
        return $counts;
    }

    private function countPublishedByMonth(string $table, int $year): array
    {
        $counts = [];
        for ($m = 1; $m <= 12; $m++) {
            $counts[] = DB::table($table)->where('status', 'published')->whereNull('deleted_at')->whereYear('published_at', $year)->whereMonth('published_at', $m)->count();
        }
        return $counts;
    }

    private function countInRange(string $table, string $column, string $start, string $end): int
    {
        return DB::table($table)->when($table !== 'visits', fn ($q) => $q->whereNull('deleted_at'))->whereBetween($column, [$start, $end])->count();
    }

    private function countPublishedInRange(string $table, string $start, string $end): int
    {
        return DB::table($table)->where('status', 'published')->whereNull('deleted_at')->whereBetween('published_at', [$start, $end])->count();
    }

    private function buildAmountResponse(array $labels, array $amounts): array
    {
        $nonZero = array_filter($amounts, fn ($v) => $v > 0);

        return [
            'labels' => $labels,
            'amounts' => $amounts,
            'summary' => [
                'total' => array_sum($amounts),
                'average' => count($nonZero) > 0 ? array_sum($amounts) / count($nonZero) : 0,
                'max' => count($nonZero) > 0 ? max($amounts) : 0,
                'min' => count($nonZero) > 0 ? min($nonZero) : 0,
            ],
        ];
    }
}
