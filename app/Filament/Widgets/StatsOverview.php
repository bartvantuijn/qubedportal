<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
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
        $yearlyRevenue = $subscriptions->sum(fn ($row) => $this->yearly($row));
        $monthlyRevenue = $yearlyRevenue / 12;

        $expenses = Expense::query()->get(['price', 'frequency']);
        $yearlyExpenses = $expenses->sum(fn ($row) => $this->yearly($row));
        $monthlyExpenses = $yearlyExpenses / 12;

        $yearlyProfit = $yearlyRevenue - $yearlyExpenses;
        $monthlyProfit = $monthlyRevenue - $monthlyExpenses;

        return [
            Stat::make(__('Subscriptions'), $subscriptionCount)
                ->description(__(':count this month', ['count' => $monthlySubscriptions]))
                ->descriptionIcon($monthlySubscriptions > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($monthlySubscriptions > 0 ? 'success' : 'gray'),
            Stat::make(__('Licenses'), $licenseCount)
                ->description(__(':count this month', ['count' => $monthlyLicenses]))
                ->descriptionIcon($monthlyLicenses > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($monthlyLicenses > 0 ? 'success' : 'gray'),
            Stat::make(__('Revenue'), $this->money($yearlyRevenue))
                ->description(__(':count this month', ['count' => $this->money($monthlyRevenue)]))
                ->descriptionIcon($monthlyRevenue > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($monthlyRevenue > 0 ? 'success' : 'gray'),
            Stat::make(__('Expenses'), $this->money($yearlyExpenses))
                ->description(__(':count this month', ['count' => $this->money($monthlyExpenses)]))
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color($yearlyExpenses > 0 ? 'danger' : 'gray'),
            Stat::make(__('Profit'), $this->money($yearlyProfit))
                ->description(__(':count this month', ['count' => $this->money($monthlyProfit)]))
                ->descriptionIcon($yearlyProfit >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($yearlyProfit >= 0 ? 'success' : 'danger'),
        ];
    }

    protected function yearly(object $row): float
    {
        return (float) match ($row->frequency) {
            'daily' => $row->price * 365,
            'monthly' => $row->price * 12,
            'yearly' => $row->price,
            default => $row->price * 0,
        };
    }

    protected function money(float $amount): string
    {
        return '€ ' . number_format($amount, 2, ',', '.');
    }
}
