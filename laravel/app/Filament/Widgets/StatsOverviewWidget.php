<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\UserActivityLog;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Throwable;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->icon(Heroicon::OutlinedUsers)
                ->color('primary'),

            Stat::make('New Users Today', User::whereDate('created_at', today())->count())
                ->description('Registered today')
                ->icon(Heroicon::OutlinedUserPlus)
                ->color('success'),

            Stat::make('Activities Today', $this->getTodayActivityCount())
                ->description('Events logged today')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->color('info'),
        ];
    }

    private function getTodayActivityCount(): int
    {
        try {
            return UserActivityLog::where('created_at', '>=', now()->startOfDay())
                ->where('created_at', '<', now()->addDay()->startOfDay())
                ->count();
        } catch (Throwable) {
            return 0;
        }
    }
}
