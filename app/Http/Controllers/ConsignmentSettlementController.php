<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentPartner;
use App\Models\ConsignmentSettlement;
use App\Models\ConsignmentSettlementItem;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class ConsignmentSettlementController extends Controller
{
    /**
     * Daftar settlement.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:draft,paid'],
            'partner_id' => ['nullable', 'integer', 'exists:consignment_partners,id'],
        ]);

        $query = ConsignmentSettlement::query()
            ->with('partner')
            ->withCount('items');

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['partner_id'])) {
            $query->where(
                'consignment_partner_id',
                $validated['partner_id']
            );
        }

        $settlements = $query
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $partners = ConsignmentPartner::query()
            ->orderBy('name')
            ->get();

        return view('consignment-settlements.index', compact(
            'settlements',
            'partners'
        ));
    }

    /**
     * Form membuat draft settlement.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => [
                'nullable',
                'integer',
                'exists:consignment_partners,id',
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ]);

        $partners = ConsignmentPartner::query()
            ->orderBy('name')
            ->get();

        $partnerId = $validated['partner_id'] ?? null;
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        $availableItems = collect();

        if ($partnerId) {
            $query = OrderItem::query()
                ->join('orders as o', 'o.id', '=', 'order_items.order_id')
                ->join('payments as pay', 'pay.order_id', '=', 'o.id')
                ->where('o.status', 'paid')
                ->where(
                    'order_items.consignment_partner_id',
                    $partnerId
                )
                ->whereNotNull('order_items.partner_unit_price')
                ->whereNotExists(function ($subquery) {
                    $subquery
                        ->selectRaw('1')
                        ->from('consignment_settlement_items as csi')
                        ->whereColumn(
                            'csi.order_item_id',
                            'order_items.id'
                        );
                });

            if ($startDate) {
                $query->whereDate('pay.paid_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('pay.paid_at', '<=', $endDate);
            }

            $availableItems = $query
                ->select([
                    'order_items.id',
                    'order_items.order_id',
                    'order_items.product_name',
                    'order_items.variant_name',
                    'order_items.quantity',
                    'order_items.partner_unit_price',
                    'order_items.partner_amount',
                    'order_items.subtotal',
                    'o.order_number',
                    'pay.paid_at',
                ])
                ->orderBy('pay.paid_at')
                ->get();
        }

        return view('consignment-settlements.create', compact(
            'partners',
            'partnerId',
            'startDate',
            'endDate',
            'availableItems'
        ));
    }

    /**
     * Simpan draft settlement dari item penjualan terpilih.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'consignment_partner_id' => [
                'required',
                'integer',
                'exists:consignment_partners,id',
            ],
            'period_start' => ['nullable', 'date'],
            'period_end' => [
                'nullable',
                'date',
                'after_or_equal:period_start',
            ],
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'integer', 'distinct'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $settlement = DB::transaction(function () use ($validated) {
            $partnerId = (int) $validated['consignment_partner_id'];

            $itemIds = collect($validated['item_ids'])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            // Kunci order item agar pemeriksaan tidak mudah berbenturan
            // dengan proses settlement lain.
            $items = OrderItem::query()
                ->whereIn('id', $itemIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($items->count() !== $itemIds->count()) {
                throw ValidationException::withMessages([
                    'item_ids' => 'Sebagian item penjualan tidak ditemukan.',
                ]);
            }

            foreach ($items as $item) {
                if ((int) $item->consignment_partner_id !== $partnerId) {
                    throw ValidationException::withMessages([
                        'item_ids' => 'Semua item harus berasal dari mitra yang sama.',
                    ]);
                }

                if (
                    $item->partner_unit_price === null ||
                    (float) $item->partner_unit_price < 0
                ) {
                    throw ValidationException::withMessages([
                        'item_ids' => 'Harga mitra pada salah satu item tidak valid.',
                    ]);
                }

                $orderIsPaid = DB::table('orders')
                    ->where('id', $item->order_id)
                    ->where('status', 'paid')
                    ->exists();

                if (!$orderIsPaid) {
                    throw ValidationException::withMessages([
                        'item_ids' => 'Hanya item dari transaksi yang sudah dibayar yang dapat disettlement.',
                    ]);
                }

                $alreadySettled = ConsignmentSettlementItem::query()
                    ->where('order_item_id', $item->id)
                    ->exists();

                if ($alreadySettled) {
                    throw ValidationException::withMessages([
                        'item_ids' => "Item penjualan #{$item->id} sudah masuk settlement.",
                    ]);
                }
            }

            $totalAmount = $items->sum(function ($item) {
                return round(
                    (float) $item->partner_unit_price * (int) $item->quantity,
                    2
                );
            });

            $settlement = ConsignmentSettlement::create([
                'settlement_number' => $this->generateSettlementNumber(),
                'consignment_partner_id' => $partnerId,
                'period_start' => $validated['period_start'] ?? null,
                'period_end' => $validated['period_end'] ?? null,
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $partnerUnitPrice = round(
                    (float) $item->partner_unit_price,
                    2
                );

                $quantity = (int) $item->quantity;

                ConsignmentSettlementItem::create([
                    'consignment_settlement_id' => $settlement->id,
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $quantity,
                    'partner_unit_price' => $partnerUnitPrice,
                    'total_amount' => round(
                        $partnerUnitPrice * $quantity,
                        2
                    ),
                ]);
            }

            return $settlement;
        });

        return redirect()
            ->route('consignment-settlements.index')
            ->with(
                'success',
                "Draft settlement {$settlement->settlement_number} berhasil dibuat."
            );
    }

    /**
     * Detail settlement.
     */
    public function show(ConsignmentSettlement $settlement)
    {
        $settlement->load([
            'partner',
            'creator',
            'items.orderItem',
        ]);

        return view('consignment-settlements.show', compact('settlement'));
    }

    /**
     * Konfirmasi pembayaran settlement.
     */
    public function pay(Request $request, ConsignmentSettlement $settlement)
    {
        $validated = $request->validate([
            'payment_method' => [
                'required',
                Rule::in(['cash', 'transfer', 'qris']),
            ],
            'payment_reference' => [
                'nullable',
                'string',
                'max:100',
            ],
            'paid_at' => [
                'required',
                'date',
            ],
        ]);

        DB::transaction(function () use ($settlement, $validated) {
            $lockedSettlement = ConsignmentSettlement::query()
                ->whereKey($settlement->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSettlement->status !== 'draft') {
                throw ValidationException::withMessages([
                    'payment_method' => 'Settlement ini sudah diproses atau bukan berstatus draft.',
                ]);
            }

            $items = ConsignmentSettlementItem::query()
                ->where('consignment_settlement_id', $lockedSettlement->id)
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Settlement tidak memiliki item. Pembayaran tidak dapat diproses.',
                ]);
            }

            // Hitung ulang total dari item settlement sebagai sumber nilai pembayaran.
            $totalAmount = round(
                $items->sum(fn ($item) => (float) $item->total_amount),
                2
            );

            if ($totalAmount <= 0) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Total settlement harus lebih besar dari nol.',
                ]);
            }

            $lockedSettlement->update([
                'total_amount' => $totalAmount,
                'status' => 'paid',
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'paid_at' => Carbon::parse($validated['paid_at']),
            ]);
        });

        return redirect()
            ->route('consignment-settlements.show', $settlement->id)
            ->with(
                'success',
                "Pembayaran settlement {$settlement->settlement_number} berhasil dicatat."
            );
    }

    /**
     * Nomor settlement unik tanpa menggunakan count() + 1.
     */
    private function generateSettlementNumber(): string
    {
        do {
            $number = 'STL-' . now()->format('Ymd') . '-'
                . Str::upper(Str::random(6));
        } while (
            ConsignmentSettlement::where(
                'settlement_number',
                $number
            )->exists()
        );

        return $number;
    }
}
