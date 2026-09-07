<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DateRange
{
    public static function presets(): array
    {
        return [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            '7days' => 'Last 7 Days',
            '30days' => 'Last 30 Days / 1 Month',
            '90days' => 'Last 90 Days',
            '1year' => 'Last 1 Year',
            'custom' => 'Custom Date Range',
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public static function fromRequest(Request $request, string $default = 'today'): array
    {
        $range = $request->input('range', $default);
        $today = Carbon::today();

        return match ($range) {
            'yesterday' => [$today->copy()->subDay()->startOfDay(), $today->copy()->subDay()->endOfDay(), 'yesterday'],
            '7days' => [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay(), '7days'],
            '30days' => [$today->copy()->subDays(29)->startOfDay(), $today->copy()->endOfDay(), '30days'],
            '90days' => [$today->copy()->subDays(89)->startOfDay(), $today->copy()->endOfDay(), '90days'],
            '1year' => [$today->copy()->subYear()->addDay()->startOfDay(), $today->copy()->endOfDay(), '1year'],
            'custom' => [
                Carbon::parse($request->input('from', $today->toDateString()))->startOfDay(),
                Carbon::parse($request->input('to', $today->toDateString()))->endOfDay(),
                'custom',
            ],
            default => [$today->copy()->startOfDay(), $today->copy()->endOfDay(), 'today'],
        };
    }
}
