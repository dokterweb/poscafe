<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard Admin
     */
    public function indexadmin()
    {
        // ======================================================
        // DATE
        // ======================================================

        $today = Carbon::today();

        $startOfMonth = Carbon::now()
            ->startOfMonth();

        $endOfMonth = Carbon::now()
            ->endOfMonth();


        // ======================================================
        // PENJUALAN HARI INI
        // ======================================================

        $salesToday = Sale::query()
            ->whereDate(
                'date',
                $today
            )
            ->count();


        $salesTodayAmount = Sale::query()
            ->whereDate(
                'date',
                $today
            )
            ->sum('final_price');


        // ======================================================
        // PENJUALAN BULAN INI
        // ======================================================

        $salesThisMonth = Sale::query()
            ->whereBetween(
                'date',
                [
                    $startOfMonth->toDateString(),
                    $endOfMonth->toDateString(),
                ]
            )
            ->count();


        $salesThisMonthAmount = Sale::query()
            ->whereBetween(
                'date',
                [
                    $startOfMonth->toDateString(),
                    $endOfMonth->toDateString(),
                ]
            )
            ->sum('final_price');


        // ======================================================
        // PEMBELIAN BULAN INI
        // ======================================================

        $purchasesThisMonth = Purchase::query()
            ->whereBetween(
                'date',
                [
                    $startOfMonth->toDateString(),
                    $endOfMonth->toDateString(),
                ]
            )
            ->count();


        $purchasesThisMonthAmount = Purchase::query()
            ->whereBetween(
                'date',
                [
                    $startOfMonth->toDateString(),
                    $endOfMonth->toDateString(),
                ]
            )
            ->sum('total');


        // ======================================================
        // TOTAL MASTER DATA
        // ======================================================

        $totalProducts = Product::query()
            ->where('is_active', true)
            ->count();


        $totalCustomers = Customer::count();

        $totalSuppliers = Supplier::count();


        // ======================================================
        // TOTAL STOCK
        // ======================================================

        $totalStock = Product::query()
            ->where('is_active', true)
            ->sum('stock');


        // ======================================================
        // TRANSAKSI BELUM LUNAS
        // ======================================================

        $unpaidSales = Sale::query()
            ->where(
                'payment_status',
                'belum_lunas'
            )
            ->count();


        $unpaidSalesAmount = Sale::query()
            ->where(
                'payment_status',
                'belum_lunas'
            )
            ->sum('final_price');


        // ======================================================
        // GRAFIK PENJUALAN 7 HARI TERAKHIR
        // ======================================================

        $salesChart = collect();

        for ($i = 6; $i >= 0; $i--) {

            $date = Carbon::today()
                ->subDays($i);


            $amount = Sale::query()
                ->whereDate(
                    'date',
                    $date
                )
                ->sum('final_price');


            $salesChart->push([

                'date' =>
                    $date->format('Y-m-d'),

                'label' =>
                    $date->translatedFormat('D'),

                'amount' =>
                    (float) $amount,

            ]);
        }


        // ======================================================
        // PENJUALAN TERBARU
        // ======================================================

        $recentSales = Sale::query()
            ->with([
                'customer',
                'user',
            ])
            ->latest('id')
            ->limit(8)
            ->get();


        // ======================================================
        // PEMBELIAN TERBARU
        // ======================================================

        $recentPurchases = Purchase::query()
            ->with([
                'supplier',
                'user',
            ])
            ->latest('id')
            ->limit(8)
            ->get();


        // ======================================================
        // RETURN
        // ======================================================

        return view(
            'dashboard.admin',
            compact(
                'salesToday',
                'salesTodayAmount',

                'salesThisMonth',
                'salesThisMonthAmount',

                'purchasesThisMonth',
                'purchasesThisMonthAmount',

                'totalProducts',
                'totalCustomers',
                'totalSuppliers',

                'totalStock',

                'unpaidSales',
                'unpaidSalesAmount',

                'salesChart',

                'recentSales',
                'recentPurchases'
            )
        );
    }
}