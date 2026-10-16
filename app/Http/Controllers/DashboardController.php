<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    
    /**
     * Dashboard Penjualan PosCafe
     */
    public function indexadmin()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | PENJUALAN HARI INI
        |--------------------------------------------------------------------------
        */

        $ordersToday = Order::query()
            ->where('status', 'paid')
            ->whereHas('payment', function ($query) use ($today) {
                $query->whereDate('paid_at', $today);
            });

        $salesToday = (clone $ordersToday)->count();

        $salesTodayAmount = (clone $ordersToday)->sum('total');

        $averageTransactionToday = $salesToday > 0
            ? $salesTodayAmount / $salesToday
            : 0;

        /*
        |--------------------------------------------------------------------------
        | PENJUALAN BULAN INI
        |--------------------------------------------------------------------------
        */

        $ordersThisMonth = Order::query()
            ->where('status', 'paid')
            ->whereHas('payment', function ($query) use (
                $startOfMonth,
                $endOfMonth
            ) {
                $query->whereBetween('paid_at', [
                    $startOfMonth,
                    $endOfMonth,
                ]);
            });

        $salesThisMonth = (clone $ordersThisMonth)->count();

        $salesThisMonthAmount = (clone $ordersThisMonth)->sum('total');

        /*
        |--------------------------------------------------------------------------
        | REKAP METODE PEMBAYARAN HARI INI
        |--------------------------------------------------------------------------
        */

        $paymentSummary = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('orders.status', 'paid')
            ->whereDate('payments.paid_at', $today)
            ->selectRaw('
                payments.payment_method,
                COUNT(*) AS jumlah_transaksi,
                COALESCE(SUM(orders.total), 0) AS total
            ')
            ->groupBy('payments.payment_method')
            ->get()
            ->keyBy('payment_method');

        /*
        |--------------------------------------------------------------------------
        | GRAFIK PENJUALAN 7 HARI TERAKHIR
        |--------------------------------------------------------------------------
        */

        $chartStart = Carbon::today()->subDays(6);

        $dailySales = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('orders.status', 'paid')
            ->whereBetween('payments.paid_at', [
                $chartStart->copy()->startOfDay(),
                Carbon::today()->endOfDay(),
            ])
            ->selectRaw('
                DATE(payments.paid_at) AS tanggal,
                COALESCE(SUM(orders.total), 0) AS omzet
            ')
            ->groupByRaw('DATE(payments.paid_at)')
            ->pluck('omzet', 'tanggal');

        $salesChart = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateKey = $date->toDateString();

            $salesChart->push([
                'date' => $dateKey,
                'label' => $date->translatedFormat('D'),
                'amount' => (float) ($dailySales[$dateKey] ?? 0),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI TERBARU
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::query()
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'paid')
            ->select('orders.*')
            ->with(['payment', 'meja'])
            ->orderByDesc('payments.paid_at')
            ->limit(8)
            ->get();

        return view('dashboard.admin', compact(
            'salesToday',
            'salesTodayAmount',
            'averageTransactionToday',
            'salesThisMonth',
            'salesThisMonthAmount',
            'paymentSummary',
            'salesChart',
            'recentOrders'
        ));
    }

}