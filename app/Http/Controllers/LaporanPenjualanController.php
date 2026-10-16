<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\LaporanPenjualanExport;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPenjualanController extends Controller
{
    public function index(Request $request)
    {
       $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_akhir' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:tanggal_mulai',
            ],
            'keyword' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'in:cash,transfer,qris'],
        ]);

        $keyword = trim($validated['keyword'] ?? '');
        $paymentMethod = $validated['payment_method'] ?? '';

        $tanggalMulai = $validated['tanggal_mulai']
            ?? now()->toDateString();

        $tanggalAkhir = $validated['tanggal_akhir']
            ?? now()->toDateString();

        $mulai = Carbon::parse($tanggalMulai)->startOfDay();
        $akhir = Carbon::parse($tanggalAkhir)->endOfDay();

        $baseQuery = Order::query()
            ->where('status', 'paid')
            ->whereHas('payment', function ($query) use ($mulai, $akhir) {
                $query->whereBetween('paid_at', [$mulai, $akhir]);
            });

        if ($keyword !== '') {
            $baseQuery->where('order_number', 'like', '%' . $keyword . '%');
        }

        if ($paymentMethod !== '') {
            $baseQuery->whereHas('payment', function ($query) use ($paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            });
        }

        // Ringkasan penjualan
        $totalOmzet = (clone $baseQuery)->sum('total');

        $jumlahTransaksi = (clone $baseQuery)->count();

        $rataRataTransaksi = $jumlahTransaksi > 0
            ? $totalOmzet / $jumlahTransaksi
            : 0;

        // Rekap berdasarkan metode pembayaran
        $rekapPembayaran = (clone $baseQuery)
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->selectRaw('
                payments.payment_method,
                COUNT(orders.id) as jumlah_transaksi,
                SUM(orders.total) as total
            ')
            ->groupBy('payments.payment_method')
            ->get()
            ->keyBy('payment_method');

        // Riwayat transaksi
        $transaksi = (clone $baseQuery)
            ->with('payment')
            ->orderByDesc(Payment::select('paid_at')
                    ->whereColumn('payments.order_id', 'orders.id')
                    ->limit(1)
            )
            ->paginate(15)
            ->withQueryString();

        return view('pos.laporan-penjualan.index', compact(
            'tanggalMulai',
            'tanggalAkhir',
            'totalOmzet',
            'jumlahTransaksi',
            'rataRataTransaksi',
            'rekapPembayaran',
            'transaksi',
            'keyword',
            'paymentMethod'
        ));
    }

    
    public function export(Request $request)
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_akhir' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:tanggal_mulai',
            ],
            'keyword' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'in:cash,transfer,qris'],
        ]);

        $tanggalMulai = $validated['tanggal_mulai']
            ?? now()->toDateString();

        $tanggalAkhir = $validated['tanggal_akhir']
            ?? now()->toDateString();

        $keyword = trim($validated['keyword'] ?? '');
        $paymentMethod = $validated['payment_method'] ?? '';

        $export = new LaporanPenjualanExport(
            $tanggalMulai,
            $tanggalAkhir,
            $keyword,
            $paymentMethod
        );

        return Excel::download(
            $export,
            'laporan-penjualan-' .
            $tanggalMulai . '-' . $tanggalAkhir . '.xlsx'
        );
    }

}
