<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $perDay = Lead::query()
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $chart = collect(range(13, 0))
            ->map(fn (int $daysAgo) => (int) ($perDay[now()->subDays($daysAgo)->toDateString()] ?? 0))
            ->all();

        return [
            Stat::make('Новые заявки', Lead::query()->where('status', LeadStatus::New)->count())
                ->description('Ждут обработки')
                ->color('danger'),
            Stat::make('Заявок сегодня', Lead::query()->whereDate('created_at', today())->count()),
            Stat::make('За 14 дней', array_sum($chart))
                ->chart($chart)
                ->color('primary'),
        ];
    }
}
