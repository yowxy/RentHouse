<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
// use Illuminate\Support\Number as SupportNumber;
use Number;

class StatsOverview extends BaseWidget

{

    private function getPrecentage(int $from, int $to)
    {
        if ($from === 0) {
            return $to > 0 ? 100 : 0; // Jika bulan lalu 0, maka persentase kenaikan 100%
        }

        return (($to - $from) / $from) * 100; // Rumus persentase pertumbuhan
    }

    protected function getStats(): array
    {
        $newListing = Listing::count();

        // Ambil semua transaksi yang disetujui untuk saat ini
        $transactions = Transaction::whereStatus('approved')->get();
        $transaction = $transactions->count(); // Menghitung jumlah transaksi yang disetujui
        $totalRevenue = $transactions->sum('total_price'); // Menjumlahkan total harga transaksi yang disetujui

        // Ambil transaksi bulan lalu
        $prevTransactions = Transaction::whereStatus('approved')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subYear()->year)
            ->first()
            ->get();

        $prevTransaction = $prevTransactions->count(); // Menghitung jumlah transaksi bulan lalu
        $prevRevenue = $prevTransactions->sum('total_price'); // Menjumlahkan total harga transaksi bulan lalu

        // Menghitung persentase perubahan transaksi dan pendapatan
        $transactionPercentage = $this->getPrecentage($prevTransaction, $transaction);
        $revenuePercentage = $this->getPrecentage($prevRevenue, $totalRevenue);

        return [
            Stat::make('New Listing of the month', $newListing),
            Stat::make('Transaction', $transaction)
                ->description($transactionPercentage > 0 ? "{$transactionPercentage}% increased" : "{$transactionPercentage}% decreased")
                ->descriptionIcon($transactionPercentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($transactionPercentage > 0 ? 'success' : 'danger'),

            Stat::make('Revenue Percentage', Number::currency($totalRevenue, 'USD'))
                ->description($revenuePercentage > 0 ? "{$revenuePercentage}% increased" : "{$revenuePercentage}% decreased")
                ->descriptionIcon($revenuePercentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenuePercentage > 0 ? 'success' : 'danger'),
        ];
    }
}
