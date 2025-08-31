<?php

namespace App\Filament\Widgets;

use App\Models\License;
use App\Models\Subscription;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $subscriptionCount = Subscription::count();
        $monthlySubscriptions = Subscription::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();

        $licenseCount = License::count();
        $monthlyLicenses = License::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();

        $subscriptions = Subscription::query()->get(['price', 'frequency']);
        $yearlyRevenue = $subscriptions->sum(function ($row) {
            return match ($row->frequency) {
                'daily' => $row->price * 365,
                'monthly' => $row->price * 12,
                'yearly' => $row->price,
                default => $row->price * 0,
            };
        });

        $monthlyRevenue = $subscriptions->sum(function ($row) {
            return match ($row->frequency) {
                'daily' => $row->price * 365 / 12,
                'monthly' => $row->price * 12 / 12,
                'yearly' => $row->price / 12,
                default => $row->price * 0,
            };
        });

        return [
            Stat::make(__('Subscriptions'), $subscriptionCount)
                ->description(__(':count this month', ['count' => $monthlySubscriptions]))
                ->descriptionIcon($monthlySubscriptions > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($monthlySubscriptions > 0 ? 'success' : 'gray'),
            Stat::make(__('Licenses'), $licenseCount)
                ->description(__(':count this month', ['count' => $monthlyLicenses]))
                ->descriptionIcon($monthlyLicenses > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($monthlyLicenses > 0 ? 'success' : 'gray'),
            Stat::make(__('Yearly'), $this->money($yearlyRevenue))
                ->description(__(':count this month', ['count' => $this->money($monthlyRevenue)]))
                ->descriptionIcon($monthlyRevenue > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($monthlyRevenue > 0 ? 'success' : 'gray'),
        ];
    }

    protected function money(float $amount): string
    {
        return '€ ' . number_format($amount, 2, ',', '.');
    }
}
