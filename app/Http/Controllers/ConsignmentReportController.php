<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsignmentReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ]);

        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        /*
         * Sumber laporan:
         * - Hanya order berstatus paid.
         * - Hanya item produk konsinyasi.
         * - Hak mitra dan keuntungan menggunakan snapshot order_items.
         * - Pembayaran mitra dihitung dari settlement berstatus paid.
         *
         * Settlement item bersifat unik per order_item, sehingga
         * satu item penjualan tidak dapat dilunasi dua kali.
         */
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join(
                'consignment_partners as cp',
                'cp.id',
                '=',
                'oi.consignment_partner_id'
            )
            ->leftJoin('payments as pay', 'pay.order_id', '=', 'o.id')
            ->leftJoin(
                'consignment_settlement_items as csi',
                'csi.order_item_id',
                '=',
                'oi.id'
            )
            ->leftJoin(
                'consignment_settlements as cs',
                'cs.id',
                '=',
                'csi.consignment_settlement_id'
            )
            ->where('o.status', 'paid')
            ->whereNotNull('oi.consignment_partner_id');

        if ($startDate) {
            $query->whereDate('pay.paid_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('pay.paid_at', '<=', $endDate);
        }

        $reports = $query
            ->select(
                'cp.id as partner_id',
                'cp.name as partner_name'
            )
           ->selectRaw('
                cp.id as partner_id,
                cp.name as partner_name,
                COUNT(DISTINCT oi.id) as transaction_items,
                COALESCE(SUM(oi.quantity), 0) as total_quantity,
                COALESCE(SUM(oi.subtotal), 0) as sales_amount,
                COALESCE(SUM(oi.partner_amount), 0) as partner_amount,
                COALESCE(SUM(oi.cafe_profit), 0) as cafe_profit
            ')
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN cs.status = 'paid'
                            THEN csi.total_amount
                            ELSE 0
                        END
                    ),
                    0
                ) as settled_amount
            ")
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN cs.status = 'draft'
                            THEN csi.total_amount
                            ELSE 0
                        END
                    ),
                    0
                ) as draft_amount
            ")
            ->groupBy('cp.id', 'cp.name')
            ->orderBy('cp.name')
            ->get()
            ->map(function ($row) {
                $row->outstanding_amount = max(
                    0,
                    (float) $row->partner_amount
                    - (float) $row->settled_amount
                );

                return $row;
            });

       $summary = [
            'sales_amount' => $reports->sum('sales_amount'),
            'partner_amount' => $reports->sum('partner_amount'),
            'cafe_profit' => $reports->sum('cafe_profit'),
            'settled_amount' => $reports->sum('settled_amount'),
            'draft_amount' => $reports->sum('draft_amount'),
            'outstanding_amount' => $reports->sum('outstanding_amount'),
            'total_quantity' => $reports->sum('total_quantity'),
            'transaction_items' => $reports->sum('transaction_items'),
        ];

        return view('consignment-reports.index', compact(
            'reports',
            'summary',
            'startDate',
            'endDate'
        ));
    }
}
