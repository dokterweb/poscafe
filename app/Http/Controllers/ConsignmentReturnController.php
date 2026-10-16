<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentPartner;
use App\Models\ConsignmentReturn;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\ConsignmentSettlement;
use App\Models\ConsignmentSettlementItem;
use App\Services\StockMovementService;
use Illuminate\Support\Carbon;
use RuntimeException;

class ConsignmentReturnController extends Controller
{
    /**
     * Daftar retur konsinyasi.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => [
                'nullable',
                'integer',
                'exists:consignment_partners,id',
            ],
            'status' => [
                'nullable',
                Rule::in(['draft', 'processed', 'cancelled']),
            ],
            'return_type' => [
                'nullable',
                Rule::in(['stock_return', 'sales_return']),
            ],
        ]);

        $query = ConsignmentReturn::query()
            ->with(['partner', 'creator'])
            ->withCount('items');

        if (!empty($validated['partner_id'])) {
            $query->where(
                'consignment_partner_id',
                $validated['partner_id']
            );
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['return_type'])) {
            $query->where('return_type', $validated['return_type']);
        }

        $returns = $query
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $partners = ConsignmentPartner::query()
            ->orderBy('name')
            ->get();

        return view('consignment-returns.index', compact(
            'returns',
            'partners'
        ));
    }

    /**
     * Form pembuatan retur.
     */
   public function create()
    {
        $partners = ConsignmentPartner::query()
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('product_type', 'consignment')
            ->where('track_stock', true)
            ->whereNotNull('consignment_partner_id')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'stock',
                'consignment_partner_id',
                'partner_price',
            ]);

        $orderItems = OrderItem::query()
            ->join('orders as o', 'o.id', '=', 'order_items.order_id')
            ->where('o.status', 'paid')
            ->whereNotNull('order_items.consignment_partner_id')
            ->whereNotNull('order_items.partner_unit_price')
            ->orderByDesc('o.id')
            ->get([
                'order_items.id',
                'order_items.order_id',
                'order_items.consignment_partner_id',
                'order_items.product_name',
                'order_items.quantity',
                'order_items.partner_unit_price',
                'o.order_number',
            ]);

        return view('consignment-returns.create', compact(
            'partners',
            'products',
            'orderItems'
        ));
    }

    /**
     * Simpan draft retur.
     *
     * Draft belum mengubah stok maupun nilai settlement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'consignment_partner_id' => [
                'required',
                'integer',
                'exists:consignment_partners,id',
            ],

            'return_date' => ['required', 'date'],

            'return_type' => [
                'required',
                Rule::in(['stock_return', 'sales_return']),
            ],

            'reason' => ['required', 'string', 'max:100'],

            'financial_treatment' => [
                'required',
                Rule::in([
                    'no_adjustment',
                    'deduct_from_draft',
                    'credit_next_settlement',
                    'partner_responsibility',
                ]),
            ],

            'notes' => ['nullable', 'string', 'max:5000'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],

            'items.*.order_item_id' => [
                'nullable',
                'integer',
                'exists:order_items,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:999999',
            ],
        ]);

        $return = DB::transaction(function () use ($validated) {
            $partnerId = (int) $validated['consignment_partner_id'];

            $returnType = $validated['return_type'];
            $treatment = $validated['financial_treatment'];

            if ($treatment === 'credit_next_settlement') {
                throw ValidationException::withMessages([
                    'financial_treatment' =>
                        'Kredit settlement berikutnya belum tersedia. Gunakan kebijakan lain sampai fitur kredit selesai dibuat.',
                ]);
            }

            if (
                $returnType === 'stock_return' &&
                $treatment !== 'no_adjustment'
            ) {
                throw ValidationException::withMessages([
                    'financial_treatment' =>
                        'Retur stok yang belum terjual harus menggunakan kebijakan tanpa penyesuaian hak mitra.',
                ]);
            }

            if (
                $returnType === 'sales_return' &&
                $treatment === 'no_adjustment'
            ) {
                throw ValidationException::withMessages([
                    'financial_treatment' =>
                        'Untuk retur penjualan, pilih kebijakan finansial yang sesuai.',
                ]);
            }

            $return = ConsignmentReturn::create([
                'return_number' => $this->generateReturnNumber(),
                'consignment_partner_id' => $partnerId,
                'return_date' => $validated['return_date'],
                'return_type' => $returnType,
                'reason' => $validated['reason'],
                'financial_treatment' => $treatment,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $index => $row) {
                $quantity = (int) $row['quantity'];
                $product = null;
                $orderItem = null;
                $settlementId = null;
                $unitPrice = 0;

                if ($returnType === 'stock_return') {
                    if (empty($row['product_id'])) {
                        throw ValidationException::withMessages([
                            "items.{$index}.product_id" =>
                                'Pilih produk untuk retur stok.',
                        ]);
                    }

                    $product = Product::query()
                        ->whereKey($row['product_id'])
                        ->where('product_type', 'consignment')
                        ->where('consignment_partner_id', $partnerId)
                        ->first();

                    if (!$product) {
                        throw ValidationException::withMessages([
                            "items.{$index}.product_id" =>
                                'Produk tidak ditemukan atau bukan milik mitra yang dipilih.',
                        ]);
                    }

                    if (!$product->track_stock) {
                        throw ValidationException::withMessages([
                            "items.{$index}.product_id" =>
                                'Produk ini tidak menggunakan pelacakan stok.',
                        ]);
                    }

                    $unitPrice = (float) $product->partner_price;
                } else {
                    if (empty($row['order_item_id'])) {
                        throw ValidationException::withMessages([
                            "items.{$index}.order_item_id" =>
                                'Pilih item penjualan untuk retur penjualan.',
                        ]);
                    }

                    $orderItem = OrderItem::query()
                        ->whereKey($row['order_item_id'])
                        ->where('consignment_partner_id', $partnerId)
                        ->first();

                    if (!$orderItem) {
                        throw ValidationException::withMessages([
                            "items.{$index}.order_item_id" =>
                                'Item penjualan tidak ditemukan untuk mitra ini.',
                        ]);
                    }

                    $orderIsPaid = DB::table('orders')
                        ->where('id', $orderItem->order_id)
                        ->where('status', 'paid')
                        ->exists();

                    if (!$orderIsPaid) {
                        throw ValidationException::withMessages([
                            "items.{$index}.order_item_id" =>
                                'Retur penjualan hanya dapat mengacu pada order yang sudah dibayar.',
                        ]);
                    }

                    if ($orderItem->partner_unit_price === null) {
                        throw ValidationException::withMessages([
                            "items.{$index}.order_item_id" =>
                                'Harga mitra pada item penjualan tidak tersedia.',
                        ]);
                    }

                    // Jumlah retur yang sudah tercatat dan belum dibatalkan.
                    $previousReturns = DB::table('consignment_return_items as cri')
                        ->join(
                            'consignment_returns as cr',
                            'cr.id',
                            '=',
                            'cri.consignment_return_id'
                        )
                        ->where('cri.order_item_id', $orderItem->id)
                        ->whereIn('cr.status', ['draft', 'processed'])
                        ->sum('cri.quantity');

                    if ($previousReturns + $quantity > (int) $orderItem->quantity) {
                        throw ValidationException::withMessages([
                            "items.{$index}.quantity" =>
                                'Jumlah retur melebihi sisa kuantitas item penjualan yang belum diretur.',
                        ]);
                    }

                    $product = $orderItem->product_id
                        ? Product::find($orderItem->product_id)
                        : null;

                    $settlementItem = DB::table('consignment_settlement_items as csi')
                        ->join(
                            'consignment_settlements as cs',
                            'cs.id',
                            '=',
                            'csi.consignment_settlement_id'
                        )
                        ->where('csi.order_item_id', $orderItem->id)
                        ->select([
                            'cs.id',
                            'cs.status',
                        ])
                        ->first();

                    $settlementId = $settlementItem?->id;
                    $unitPrice = (float) $orderItem->partner_unit_price;

                    if (
                        $treatment === 'deduct_from_draft' &&
                        (!$settlementItem || $settlementItem->status !== 'draft')
                    ) {
                        throw ValidationException::withMessages([
                            'financial_treatment' =>
                                'Kebijakan pengurangan hanya dapat dipilih untuk item yang berada dalam settlement draft.',
                        ]);
                    }

                   if (
                        $treatment === 'credit_next_settlement' &&
                        (!$settlementItem || $settlementItem->status !== 'paid')
                    ) {
                        throw ValidationException::withMessages([
                            'financial_treatment' =>
                                'Kredit settlement berikutnya hanya berlaku untuk item dalam settlement yang sudah dibayar.',
                        ]);
                    }
                }

                $productName = $returnType === 'stock_return'
                    ? $product->name
                    : $orderItem->product_name;

                $productId = $returnType === 'stock_return'
                    ? $product->id
                    : $orderItem->product_id;

                $totalAmount = round($unitPrice * $quantity, 2);

                $return->items()->create([
                    'product_id' => $productId,
                    'order_item_id' => $orderItem?->id,
                    'consignment_settlement_id' => $settlementId,
                    'product_name' => $productName,
                    'quantity' => $quantity,
                    'partner_unit_price' => $unitPrice,
                    'total_amount' => $totalAmount,
                ]);
            }

            return $return;
        });

        return redirect()
            ->route('consignment-returns.index')
            ->with(
                'success',
                "Draft retur {$return->return_number} berhasil dibuat."
            );
    }

    public function show(ConsignmentReturn $consignmentReturn)
    {
        $consignmentReturn->load([
            'partner',
            'creator',
            'items.product',
            'items.orderItem',
            'items.settlement',
        ]);

        return view('consignment-returns.show', [
            'return' => $consignmentReturn,
        ]);
    }

    
    /**
     * Proses retur yang masih berstatus draft.
     */
    public function process(
        ConsignmentReturn $consignmentReturn,
        StockMovementService $stockMovementService
    ) {
        DB::transaction(function () use (
            $consignmentReturn,
            $stockMovementService
        ) {
            $return = ConsignmentReturn::query()
                ->whereKey($consignmentReturn->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($return->status !== 'draft') {
                throw ValidationException::withMessages([
                    'return' => 'Retur ini sudah diproses atau dibatalkan.',
                ]);
            }

            /*
            * Kredit settlement berikutnya memerlukan ledger penyesuaian
            * agar kredit dapat dilacak dan tidak digunakan dua kali.
            * Jangan memprosesnya sebelum ledger tersebut tersedia.
            */
            if ($return->financial_treatment === 'credit_next_settlement') {
                throw ValidationException::withMessages([
                    'financial_treatment' =>
                        'Kredit settlement berikutnya belum dapat diproses. Mekanisme saldo kredit perlu disiapkan terlebih dahulu.',
                ]);
            }

            $items = $return->items()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'return' => 'Retur tidak memiliki item.',
                ]);
            }

            foreach ($items as $item) {
                if ($return->return_type === 'stock_return') {
                    /*
                    * Barang yang belum terjual dikembalikan kepada mitra.
                    * Stok kafe berkurang dan movement tercatat.
                    */
                    $product = Product::query()
                        ->whereKey($item->product_id)
                        ->where('product_type', 'consignment')
                        ->where(
                            'consignment_partner_id',
                            $return->consignment_partner_id
                        )
                        ->lockForUpdate()
                        ->first();

                    if (!$product || !$product->track_stock) {
                        throw ValidationException::withMessages([
                            'return' =>
                                "Produk {$item->product_name} tidak valid untuk retur stok.",
                        ]);
                    }

                   try {
                        $stockMovementService->out(
                            product: $product,
                            qty: (int) $item->quantity,
                            referenceType: 'consignment_return',
                            referenceId: $return->id,
                            note: "Retur {$return->return_number}: {$return->reason}",
                            userId: auth()->id()
                        );
                    } catch (RuntimeException $e) {
                        throw ValidationException::withMessages([
                            'return' => $e->getMessage(),
                        ]);
                    }

                    continue;
                }

                /*
                * Retur yang berkaitan dengan penjualan harus mengacu
                * pada item penjualan yang masih valid.
                */
                $orderItem = OrderItem::query()
                    ->whereKey($item->order_item_id)
                    ->lockForUpdate()
                    ->first();

                if (
                    !$orderItem ||
                    (int) $orderItem->consignment_partner_id
                        !== (int) $return->consignment_partner_id
                ) {
                    throw ValidationException::withMessages([
                        'return' =>
                            "Referensi penjualan untuk {$item->product_name} tidak valid.",
                    ]);
                }

                $orderIsPaid = DB::table('orders')
                    ->where('id', $orderItem->order_id)
                    ->where('status', 'paid')
                    ->exists();

                if (!$orderIsPaid) {
                    throw ValidationException::withMessages([
                        'return' =>
                            'Item retur harus berasal dari order yang sudah dibayar.',
                    ]);
                }

                // Hitung retur dari dokumen lain yang belum dibatalkan.
                $otherReturns = DB::table('consignment_return_items as cri')
                    ->join(
                        'consignment_returns as cr',
                        'cr.id',
                        '=',
                        'cri.consignment_return_id'
                    )
                    ->where('cri.order_item_id', $orderItem->id)
                    ->where('cr.id', '!=', $return->id)
                    ->whereIn('cr.status', ['draft', 'processed'])
                    ->sum('cri.quantity');

                // Hitung semua kuantitas pada dokumen retur yang sedang diproses.
                $currentReturnQty = $return->items()
                    ->where('order_item_id', $orderItem->id)
                    ->sum('quantity');

                if (
                    (int) $otherReturns + (int) $currentReturnQty >
                    (int) $orderItem->quantity
                ) {
                    throw ValidationException::withMessages([
                        'return' =>
                            "Total retur untuk {$orderItem->product_name} melebihi jumlah yang terjual. Periksa draft retur lain terlebih dahulu.",
                    ]);
                }

                if ($return->financial_treatment === 'deduct_from_draft') {
                    $settlementItem = ConsignmentSettlementItem::query()
                        ->where('order_item_id', $orderItem->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$settlementItem) {
                        throw ValidationException::withMessages([
                            'return' =>
                                'Item penjualan tidak ditemukan dalam settlement draft.',
                        ]);
                    }

                    $settlement = ConsignmentSettlement::query()
                        ->whereKey($settlementItem->consignment_settlement_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$settlement || $settlement->status !== 'draft') {
                        throw ValidationException::withMessages([
                            'return' =>
                                'Settlement bukan berstatus draft. Nilai settlement yang sudah dibayar tidak boleh diubah.',
                        ]);
                    }

                    if (
                        (int) $item->quantity >
                        (int) $settlementItem->quantity
                    ) {
                        throw ValidationException::withMessages([
                            'return' =>
                                'Jumlah retur melebihi kuantitas yang masih ada dalam settlement draft.',
                        ]);
                    }

                    $remainingQty =
                        (int) $settlementItem->quantity -
                        (int) $item->quantity;

                    if ($remainingQty === 0) {
                        $settlementItem->delete();
                    } else {
                        $settlementItem->update([
                            'quantity' => $remainingQty,
                            'total_amount' => round(
                                $remainingQty *
                                (float) $settlementItem->partner_unit_price,
                                2
                            ),
                        ]);
                    }

                    $newTotal = ConsignmentSettlementItem::query()
                        ->where(
                            'consignment_settlement_id',
                            $settlement->id
                        )
                        ->sum('total_amount');

                    $settlement->update([
                        'total_amount' => round((float) $newTotal, 2),
                    ]);
                }

                /*
                * Untuk partner_responsibility, retur dicatat sebagai
                * tanggung jawab mitra tanpa mengubah settlement.
                *
                * Untuk sales_return, stok tidak otomatis ditambah atau
                * dikurangi. Penanganan fisik barang harus mengikuti
                * proses retur penjualan yang berlaku di kafe.
                */
            }

            $return->update([
                'status' => 'processed',
                'processed_at' => Carbon::now(),
            ]);
        });

        return redirect()
            ->route('consignment-returns.show', $consignmentReturn->id)
            ->with(
                'success',
                "Retur {$consignmentReturn->return_number} berhasil diproses."
            );
    }


    private function generateReturnNumber(): string
    {
        do {
            $number = 'RET-' . now()->format('Ymd') . '-'
                . Str::upper(Str::random(6));
        } while (
            ConsignmentReturn::where('return_number', $number)->exists()
        );

        return $number;
    }
}
