<?php

namespace App\Filament\Widgets;

use App\Models\AccountForSale;
use App\Models\Order;
use App\Models\SellerApplication;
use App\Models\User;
use App\Models\Withdrawal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $completedOrders = Order::query()->where('status', 'completed');
        $pendingPayments = Order::query()->where('status', 'pending');
        $pendingApplications = SellerApplication::query()->where('status', 'pending')->count();
        $pendingWithdrawals = Withdrawal::query()->where('status', 'pending')->count();

        return [
            Stat::make('Users', User::query()->count())
                ->description(
                    User::query()->where('role', 'buyer')->count() . ' buyers · ' .
                    User::query()->where('role', 'seller')->count() . ' sellers'
                ),
            Stat::make('Pending Applications', $pendingApplications)
                ->description('Seller requests awaiting review')
                ->color($pendingApplications > 0 ? 'warning' : 'success'),
            Stat::make('Active Listings', AccountForSale::query()->where('status', 'available')->count())
                ->description(
                    AccountForSale::query()->where('status', 'sold')->count() . ' sold listings'
                ),
            Stat::make('Completed Revenue', number_format((float) $completedOrders->sum('amount_dzd'), 0) . ' DA')
                ->description($completedOrders->count() . ' successful payments'),
            Stat::make('Pending Payments', $pendingPayments->count())
                ->description('Checkouts not completed yet')
                ->color($pendingPayments->count() > 0 ? 'warning' : 'gray'),
            Stat::make('Pending Withdrawals', $pendingWithdrawals)
                ->description('Seller payout requests')
                ->color($pendingWithdrawals > 0 ? 'warning' : 'success'),
        ];
    }
}
