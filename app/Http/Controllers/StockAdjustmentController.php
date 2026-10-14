<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    
    /**
     * Menampilkan histori stock adjustment.
     */
    public function index(Request $request)
    {
        // ======================================================
        // QUERY DASAR
        // HANYA MOVEMENT DENGAN reference_type = adjustment
        // ======================================================

        $query = \App\Models\StockMovement::query()
            ->with([
                'product',
                'user',
            ])
            ->where(
                'reference_type',
                'adjustment'
            );


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
        // SUMMARY
        // ======================================================

        $totalAdjustment = (clone $query)
            ->count();


        $totalIn = (clone $query)
            ->where('type', 'in')
            ->sum('qty');


        $totalOut = (clone $query)
            ->where('type', 'out')
            ->sum('qty');


        // ======================================================
        // PAGINATION
        // ======================================================

        $adjustments = $query
            ->latest('id')
            ->paginate(15)
            ->withQueryString();


        // ======================================================
        // RETURN VIEW
        // ======================================================

        return view(
            'stock-adjustments.index',
            compact(
                'adjustments',
                'totalAdjustment',
                'totalIn',
                'totalOut'
            )
        );
    }

    public function create()
    {
        return view(
            'stock-adjustments.create'
        );
    }


    /**
     * Simpan Stock Adjustment.
     */
    public function store(Request $request,StockMovementService $stockMovementService) 
    {

        $validated = $request->validate([
            'product_id' => ['required','integer','exists:products,id',],
            'type' => ['required','in:in,out',],
            'qty' => ['required','integer','min:1',],
            'note' => ['required','string','max:500',],
        ], [
            'product_id.required' =>'Produk wajib dipilih.',
            'product_id.exists' =>'Produk tidak ditemukan.','Tipe adjustment wajib dipilih.',
            'type.in' =>'Tipe adjustment tidak valid.',
            'qty.required' =>'Qty wajib diisi.',
            'qty.min' =>'Qty minimal 1.',
            'note.required' =>'Alasan adjustment wajib diisi.',
        ]);

        try {
            DB::transaction(function () use ($validated,$stockMovementService) 
            {

                // ==============================================
                // PRODUCT
                // ==============================================

                $product = Product::find(
                    $validated['product_id']
                );


                if (!$product) {

                    throw new \Exception(
                        'Produk tidak ditemukan.'
                    );

                }


                // ==============================================
                // QTY
                // ==============================================

                $qty = (int) $validated['qty'];


                // ==============================================
                // STOCK MOVEMENT
                // ==============================================

                if ($validated['type'] === 'in') {
                    $stockMovementService->in(
                        product: $product,
                        qty: $qty,
                        referenceType: 'adjustment',
                        referenceId: null,
                        note:'Adjustment Stock: ' .$validated['note'],
                        userId:auth()->id()
                    );
                } else {
                    $stockMovementService->out(
                        product: $product,
                        qty: $qty,
                        referenceType: 'adjustment',
                        referenceId: null,
                        note:'Adjustment Stock: ' .$validated['note'],
                        userId:auth()->id()
                    );
                }
            });

            return redirect()->route('stock-adjustments.create')->with('success','Stock berhasil disesuaikan.');

        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error','Stock adjustment gagal: ' .$e->getMessage());
        }
    }

    public function searchProducts(Request $request)
    {
        $search = trim(
            $request->get('search', '')
        );

        if ($search === '') {

            return response()->json([
                'products' => [],
            ]);

        }

        $products = Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('name','like','%' . $search . '%')
                ->orWhere('barcode','like','%' . $search . '%');
            })
            ->orderBy('name')->limit(10)
            ->get(['id','barcode','name','stock','hpp','price',]);

        return response()->json(['products' => $products,]);
    }

}