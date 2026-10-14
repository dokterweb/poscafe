<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
   public function index(Request $request)
    {
        // ======================================================
        // QUERY
        // ======================================================

        $query = StockMovement::query()
            ->with([
                'product',
                'user',
            ]);


        // ======================================================
        // SEARCH PRODUK
        // ======================================================

        if ($request->filled('search')) {

            $search = trim(
                $request->search
            );

            $query->whereHas(
                'product',
                function ($productQuery) use ($search) {

                    $productQuery
                        ->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'barcode',
                            'like',
                            '%' . $search . '%'
                        );

                }
            );
        }


        // ======================================================
        // FILTER TYPE
        // ======================================================

        if ($request->filled('type')) {

            $type = $request->type;

            if (
                in_array(
                    $type,
                    ['in', 'out']
                )
            ) {

                $query->where(
                    'type',
                    $type
                );

            }
        }

        // ======================================================
        // FILTER SOURCE
        // ======================================================

        if ($request->filled('source')) {

            $source = $request->source;

            if (
                in_array(
                    $source,
                    [
                        'purchase',
                        'sale',
                        'adjustment',
                    ]
                )
            ) {

                $query->where(
                    'reference_type',
                    $source
                );

            }
        }
        // ======================================================
        // FILTER TANGGAL DARI
        // ======================================================

        if ($request->filled('date_from')) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );

        }


        // ======================================================
        // FILTER TANGGAL SAMPAI
        // ======================================================

        if ($request->filled('date_to')) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );

        }


        // ======================================================
        // PAGINATION
        // ======================================================

        $movements = $query
            ->latest('id')
            ->paginate(15)
            ->withQueryString();


        // ======================================================
        // AMBIL REFERENSI TRANSAKSI
        //
        // Kita tidak menggunakan relationship polymorphic.
        // Purchase dan Sale tetap dipisahkan.
        // ======================================================

        $purchaseIds = $movements
            ->where('reference_type', 'purchase')
            ->pluck('reference_id')
            ->filter()
            ->unique()
            ->values();


        $saleIds = $movements
            ->where('reference_type', 'sale')
            ->pluck('reference_id')
            ->filter()
            ->unique()
            ->values();


        // ======================================================
        // LOAD PURCHASE
        // ======================================================

        $purchases = Purchase::whereIn(
                'id',
                $purchaseIds
            )
            ->get()
            ->keyBy('id');


        // ======================================================
        // LOAD SALE
        // ======================================================

        $sales = Sale::whereIn(
                'id',
                $saleIds
            )
            ->get()
            ->keyBy('id');


        // ======================================================
        // RETURN VIEW
        // ======================================================

        return view(
            'stock-movements.index',
            compact(
                'movements',
                'purchases',
                'sales'
            )
        );
    }
}