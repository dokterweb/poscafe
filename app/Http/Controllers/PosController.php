<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Meja;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\StockMovementService;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::query()
            ->with([
                'category',
                'consignmentPartner',
                'variants' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('name');
                },
                'optionGroups' => function ($query) {
                    $query->whereHas('options', function ($optionQuery) {
                        $optionQuery->where('is_active', true);
                    })->with([
                        'options' => function ($optionQuery) {
                            $optionQuery->where('is_active', true)
                                ->orderBy('name');
                        },
                    ]);
                },
            ])
            ->where('is_active', true)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get();

        // Meja aktif yang belum memiliki pesanan dine-in berstatus open.
        $mejas = Meja::query()
            ->where('is_active', true)
            ->whereDoesntHave('orders', function ($query) {
                $query->where('order_type', 'dine_in')
                    ->where('status', 'open');
            })
            ->orderBy('nomor')
            ->get();

        $ordersAktif = Order::query()
        ->with(['meja', 'items'])
        ->where('status', 'open')
        ->orderBy('created_at')
        ->get();
        
        return view('pos.index', compact(
            'products',
            'categories',
            'mejas',
            'search',
            'ordersAktif'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_type' => ['required', 'in:dine_in,takeaway'],
            'meja_id' => ['nullable', 'integer', 'exists:mejas,id'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.option_ids' => ['nullable', 'array'],
            'items.*.option_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:product_options,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:999',
            ],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (
            $validated['order_type'] === 'dine_in'
            && empty($validated['meja_id'])
        ) {
            throw ValidationException::withMessages([
                'meja_id' => 'Silakan pilih meja untuk pesanan dine-in.',
            ]);
        }

        if ($validated['order_type'] === 'takeaway') {
            $validated['meja_id'] = null;
        }

        $order = DB::transaction(function () use ($validated) {
            // Pastikan meja masih aktif dan belum dipakai pesanan lain.
            if ($validated['order_type'] === 'dine_in') {
                $meja = Meja::query()
                    ->whereKey($validated['meja_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$meja || !$meja->is_active) {
                    throw ValidationException::withMessages([
                        'meja_id' => 'Meja tidak aktif atau tidak ditemukan.',
                    ]);
                }

                $alreadyUsed = Order::query()
                    ->where('meja_id', $meja->id)
                    ->where('order_type', 'dine_in')
                    ->where('status', 'open')
                    ->exists();

                if ($alreadyUsed) {
                    throw ValidationException::withMessages([
                        'meja_id' => 'Meja sudah memiliki pesanan aktif. Silakan pilih meja lain.',
                    ]);
                }
            }

            $order = Order::create([
                'order_number' => 'POS-' . now()->format('YmdHis')
                    . '-' . Str::upper(Str::random(5)),
                'order_type' => $validated['order_type'],
                'meja_id' => $validated['meja_id'] ?? null,
                'status' => 'open',
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
            ]);

            $orderSubtotal = 0;

            // Menghitung total permintaan per produk dalam pesanan ini.
            $requestedStockByProduct = [];

            foreach ($validated['items'] as $itemData) {
                $product = Product::query()
                    ->where('is_active', true)
                    ->with([
                        'consignmentPartner',
                        'variants' => function ($query) {
                            $query->where('is_active', true);
                        },
                        'optionGroups.options' => function ($query) {
                            $query->where('is_active', true);
                        },
                    ])
                    ->lockForUpdate()
                    ->find($itemData['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu produk tidak aktif atau sudah dihapus.',
                    ]);
                }

                // Produk titipan belum mendukung varian atau opsi tambahan.
                if ($product->product_type === 'consignment') {
                    if (
                        $product->variants->isNotEmpty()
                        || $product->optionGroups->isNotEmpty()
                    ) {
                        throw ValidationException::withMessages([
                            'items' => "Produk titipan {$product->name} belum mendukung varian atau opsi tambahan.",
                        ]);
                    }

                    if (
                        !$product->consignmentPartner
                        || !$product->consignmentPartner->is_active
                    ) {
                        throw ValidationException::withMessages([
                            'items' => "Mitra untuk produk {$product->name} tidak aktif atau tidak ditemukan.",
                        ]);
                    }

                    if ($product->partner_price === null) {
                        throw ValidationException::withMessages([
                            'items' => "Harga mitra untuk produk {$product->name} belum ditentukan.",
                        ]);
                    }
                }

                // Validasi varian.
                $variant = null;

                if ($product->variants->isNotEmpty()) {
                    if (empty($itemData['variant_id'])) {
                        throw ValidationException::withMessages([
                            'items' => "Pilih varian untuk produk {$product->name}.",
                        ]);
                    }

                    $variant = $product->variants->firstWhere(
                        'id',
                        (int) $itemData['variant_id']
                    );

                    if (!$variant) {
                        throw ValidationException::withMessages([
                            'items' => "Varian produk {$product->name} tidak valid atau tidak aktif.",
                        ]);
                    }
                } elseif (!empty($itemData['variant_id'])) {
                    throw ValidationException::withMessages([
                        'items' => "Produk {$product->name} tidak memiliki varian tersebut.",
                    ]);
                }

                // Validasi opsi tambahan.
                $selectedOptionIds = collect($itemData['option_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $availableOptions = $product->optionGroups
                    ->flatMap(fn ($group) => $group->options);

                $selectedOptions = $availableOptions
                    ->whereIn('id', $selectedOptionIds)
                    ->values();

                if (
                    $selectedOptions->count()
                    !== $selectedOptionIds->count()
                ) {
                    throw ValidationException::withMessages([
                        'items' => "Opsi tambahan untuk produk {$product->name} tidak valid.",
                    ]);
                }

                // Hitung harga dan subtotal.
                $basePrice = (float) (
                    $variant?->price ?? $product->price
                );

                $optionsPrice = $selectedOptions->sum(
                    fn ($option) => (float) $option->price_adjustment
                );

                $unitPrice = round($basePrice + $optionsPrice, 2);
                $quantity = (int) $itemData['quantity'];

                $itemSubtotal = round($unitPrice * $quantity, 2);

                // Validasi stok jika produk menggunakan pelacakan stok.
                // Jumlah beberapa baris produk yang sama dijumlahkan.
                if ($product->track_stock) {
                    $requestedStockByProduct[$product->id] =
                        ($requestedStockByProduct[$product->id] ?? 0)
                        + $quantity;

                    $requestedTotal = $requestedStockByProduct[$product->id];

                    if ($requestedTotal > $product->stock) {
                        throw ValidationException::withMessages([
                            'items' => "Stok {$product->name} hanya tersedia "
                                . number_format($product->stock, 0, ',', '.')
                                . ' unit, sedangkan total permintaan pesanan ini '
                                . number_format($requestedTotal, 0, ',', '.')
                                . ' unit.',
                        ]);
                    }
                }

                // Nilai default untuk produk reguler.
                $consignmentData = [
                    'consignment_partner_id' => null,
                    'consignment_partner_name' => null,
                    'partner_unit_price' => null,
                    'partner_amount' => 0,
                    'cafe_profit' => 0,
                ];

                // Snapshot harga dan hak mitra untuk produk titipan.
                if ($product->product_type === 'consignment') {
                    $partnerUnitPrice = round(
                        (float) $product->partner_price,
                        2
                    );

                    if ($unitPrice < $partnerUnitPrice) {
                        throw ValidationException::withMessages([
                            'items' => "Harga jual produk {$product->name} tidak boleh lebih rendah dari harga mitra.",
                        ]);
                    }

                    $consignmentData = [
                        'consignment_partner_id' =>
                            $product->consignment_partner_id,

                        'consignment_partner_name' =>
                            $product->consignmentPartner->name,

                        'partner_unit_price' => $partnerUnitPrice,

                        'partner_amount' => round(
                            $partnerUnitPrice * $quantity,
                            2
                        ),

                        'cafe_profit' => round(
                            ($unitPrice - $partnerUnitPrice) * $quantity,
                            2
                        ),
                    ];
                }

                // Simpan detail pesanan.
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $product->name,
                    'variant_name' => $variant?->name,

                    'selected_options' => $selectedOptions
                        ->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'name' => $option->name,
                                'price_adjustment' =>
                                    (float) $option->price_adjustment,
                            ];
                        })
                        ->values()
                        ->all(),

                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                    'notes' => $itemData['notes'] ?? null,

                    'consignment_partner_id' =>
                        $consignmentData['consignment_partner_id'],

                    'consignment_partner_name' =>
                        $consignmentData['consignment_partner_name'],

                    'partner_unit_price' =>
                        $consignmentData['partner_unit_price'],

                    'partner_amount' =>
                        $consignmentData['partner_amount'],

                    'cafe_profit' =>
                        $consignmentData['cafe_profit'],
                ]);

                $orderSubtotal += $itemSubtotal;
            }

            // Perbarui total pesanan.
            $order->update([
                'subtotal' => $orderSubtotal,
                'total' => $orderSubtotal,
            ]);

            return $order;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan berhasil disimpan.',
            'redirect' => route('pos.index'),
            'order_number' => $order->order_number,
        ], 201);
    }

  
    public function showOrder(Order $order)
    {
        $order->load(['meja', 'items']);

        $products = Product::query()
            ->where('is_active', true)
            ->with([
                'variants' => function ($query) {
                    $query->where('is_active', true)->orderBy('name');
                },
                'optionGroups' => function ($query) {
                    $query->whereHas('options', function ($optionQuery) {
                        $optionQuery->where('is_active', true);
                    })->with([
                        'options' => function ($optionQuery) {
                            $optionQuery->where('is_active', true)->orderBy('name');
                        },
                    ]);
                },
            ])
            ->orderBy('name')
            ->get();

        $productsForOrder = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'track_stock' => (bool) $product->track_stock,
                'product_type' => $product->product_type,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'price' => (float) $variant->price,
                    ];
                })->values(),

                'option_groups' => $product->optionGroups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,

                        'options' => $group->options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'name' => $option->name,
                                'price_adjustment' => (float) $option->price_adjustment,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();

        return view('pos.orders.show', compact(
            'order',
            'products',
            'productsForOrder'
        ));
    }

    public function mejaIndex()
    {
        $mejas = Meja::query()
            ->where('is_active', true)
            ->with([
                'orders' => function ($query) {
                    $query
                        ->where('order_type', 'dine_in')
                        ->where('status', 'open')
                        ->with('items')
                        ->orderByDesc('id');
                },
            ])
            ->orderBy('nomor')
            ->get();

        $totalMeja = $mejas->count();

        $mejaTerisi = $mejas->filter(
            fn ($meja) => $meja->orders->isNotEmpty()
        )->count();

        $mejaKosong = $totalMeja - $mejaTerisi;

        return view('pos.mejas', compact(
            'mejas',
            'totalMeja',
            'mejaTerisi',
            'mejaKosong'
        ));
    }



    public function updateOrderItem(Request $request,Order $order,OrderItem $item
    ) {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        if ((int) $item->order_id !== (int) $order->id) {
            abort(404);
        }

        DB::transaction(function () use ($order, $item, $validated) {
            // Kunci pesanan agar tidak diubah bersamaan.
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'open') {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan ini sudah tidak dapat diubah.',
                ]);
            }

            // Pastikan item benar-benar milik pesanan ini.
            $lockedItem = $lockedOrder->items()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            $quantity = (int) $validated['quantity'];

            // Ambil dan kunci produk jika masih terhubung.
            $product = null;

            if ($lockedItem->product_id !== null) {
                $product = Product::query()
                    ->whereKey($lockedItem->product_id)
                    ->lockForUpdate()
                    ->first();
            }

            // Validasi khusus produk konsinyasi.
            if ($lockedItem->consignment_partner_id !== null) {
                if (
                    !$product ||
                    $product->product_type !== 'consignment'
                ) {
                    throw ValidationException::withMessages([
                        'quantity' => "Produk titipan {$lockedItem->product_name} tidak ditemukan atau jenis produknya telah berubah.",
                    ]);
                }

                if ($lockedItem->partner_unit_price === null) {
                    throw ValidationException::withMessages([
                        'quantity' => "Snapshot harga mitra untuk {$lockedItem->product_name} tidak tersedia.",
                    ]);
                }
            }

            // Validasi stok hanya untuk produk yang melacak stok.
            if ($product && $product->track_stock) {
                // Hitung jumlah produk yang sama pada item lain
                // dalam pesanan yang sedang dibuka.
                $otherQuantity = (int) $lockedOrder->items()
                    ->where('product_id', $product->id)
                    ->where('id', '!=', $lockedItem->id)
                    ->sum('quantity');

                $requestedTotal = $otherQuantity + $quantity;

                // Jumlah sebelumnya digunakan agar pengguna tetap
                // dapat mengurangi jumlah pesanan yang sudah berlebih.
                $previousTotal = $otherQuantity
                    + (int) $lockedItem->quantity;

                if (
                    $requestedTotal > (int) $product->stock
                    && $requestedTotal > $previousTotal
                ) {
                    $available = max(
                        0,
                        (int) $product->stock - $otherQuantity
                    );

                    throw ValidationException::withMessages([
                        'quantity' => "Stok {$product->name} hanya tersedia "
                            . number_format((int) $product->stock, 0, ',', '.')
                            . ' unit. Item lain dalam pesanan menggunakan '
                            . number_format($otherQuantity, 0, ',', '.')
                            . ' unit. Jumlah item ini maksimal '
                            . number_format($available, 0, ',', '.')
                            . ' unit.',
                    ]);
                }
            }

            // Hitung ulang subtotal berdasarkan harga satuan tersimpan.
            $subtotal = round(
                (float) $lockedItem->unit_price * $quantity,
                2
            );

            $updateData = [
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];

            // Perbarui snapshot nilai konsinyasi sesuai jumlah terbaru.
            if ($lockedItem->consignment_partner_id !== null) {
                $partnerUnitPrice = round(
                    (float) $lockedItem->partner_unit_price,
                    2
                );

                $updateData['partner_amount'] = round(
                    $partnerUnitPrice * $quantity,
                    2
                );

                $updateData['cafe_profit'] = round(
                    (
                        (float) $lockedItem->unit_price
                        - $partnerUnitPrice
                    ) * $quantity,
                    2
                );
            }

            $lockedItem->update($updateData);

            // Hitung ulang total pesanan.
            $this->recalculateOrderTotals($lockedOrder);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Jumlah item berhasil diperbarui.',
            'redirect' => route('pos.orders.show', $order->id),
        ]);
    }


    public function destroyOrderItem(Order $order, OrderItem $item)
    {
        if ($order->status !== 'open') {
            return response()->json([
                'message' => 'Pesanan ini sudah tidak dapat diubah.',
            ], 422);
        }

        if ((int) $item->order_id !== (int) $order->id) {
            abort(404);
        }

        DB::transaction(function () use ($order, $item) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'open') {
                abort(422, 'Pesanan ini sudah tidak dapat diubah.');
            }

            $lockedItem = $lockedOrder->items()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedItem->delete();

            $this->recalculateOrderTotals($lockedOrder);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Item berhasil dihapus.',
            'redirect' => route('pos.orders.show', $order->id),
        ]);
    }

    private function recalculateOrderTotals(Order $order): void
    {
        $subtotal = $order->items()->sum('subtotal');

        $order->update([
            'subtotal' => $subtotal,
            'total' => $subtotal - $order->discount + $order->tax,
        ]);
    }

    
    public function addOrderItem(Request $request, Order $order)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:product_options,id',
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $redirect = route('pos.orders.show', $order->id);

        DB::transaction(function () use ($validated, $order) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'open') {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan ini sudah tidak dapat diubah.',
                ]);
            }

            $product = Product::query()
                ->where('is_active', true)
                ->with([
                    'consignmentPartner',
                    'variants' => fn ($query) => $query->where('is_active', true),
                    'optionGroups' => function ($query) {
                        $query->whereHas('options', function ($optionQuery) {
                            $optionQuery->where('is_active', true);
                        })->with([
                            'options' => fn ($optionQuery) =>
                                $optionQuery->where('is_active', true),
                        ]);
                    },
                ])
                ->lockForUpdate()
                ->find($validated['product_id']);

            if (!$product) {
                throw ValidationException::withMessages([
                    'product_id' => 'Produk tidak aktif atau tidak ditemukan.',
                ]);
            }

            $variant = null;

            if ($product->variants->isNotEmpty()) {
                if (empty($validated['variant_id'])) {
                    throw ValidationException::withMessages([
                        'variant_id' => "Pilih varian untuk produk {$product->name}.",
                    ]);
                }

                $variant = $product->variants->firstWhere(
                    'id',
                    (int) $validated['variant_id']
                );

                if (!$variant) {
                    throw ValidationException::withMessages([
                        'variant_id' => 'Varian tidak valid atau tidak aktif.',
                    ]);
                }
            } elseif (!empty($validated['variant_id'])) {
                throw ValidationException::withMessages([
                    'variant_id' => 'Produk ini tidak memiliki varian tersebut.',
                ]);
            }

            $optionIds = collect($validated['option_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $availableOptions = $product->optionGroups
                ->flatMap(fn ($group) => $group->options);

            $selectedOptions = $availableOptions
                ->whereIn('id', $optionIds)
                ->values();

            if ($selectedOptions->count() !== $optionIds->count()) {
                throw ValidationException::withMessages([
                    'option_ids' => 'Opsi tambahan tidak valid untuk produk ini.',
                ]);
            }

            $unitPrice = (float) ($variant?->price ?? $product->price)
                + $selectedOptions->sum(
                    fn ($option) => (float) $option->price_adjustment
                );

            $quantity = (int) $validated['quantity'];
            $itemSubtotal = $unitPrice * $quantity;

            if ($product->track_stock) {
                $quantityAlreadyInOrder = (int) $lockedOrder->items()
                    ->where('product_id', $product->id)
                    ->sum('quantity');

                $requestedTotal = $quantityAlreadyInOrder + $quantity;

                if ($requestedTotal > $product->stock) {
                    throw ValidationException::withMessages([
                        'quantity' => "Stok {$product->name} hanya tersedia {$product->stock} unit. "
                            . "Pesanan ini sudah memiliki {$quantityAlreadyInOrder} unit. "
                            . "Jumlah tambahan maksimal "
                            . max(0, $product->stock - $quantityAlreadyInOrder)
                            . ' unit.',
                    ]);
                }
            }

            $consignmentData = [
                'consignment_partner_id' => null,
                'consignment_partner_name' => null,
                'partner_unit_price' => null,
                'partner_amount' => 0,
                'cafe_profit' => 0,
            ];

            if ($product->product_type === 'consignment') {
                if (
                    $product->variants->isNotEmpty()
                    || $product->optionGroups->isNotEmpty()
                ) {
                    throw ValidationException::withMessages([
                        'product_id' => "Produk titipan {$product->name} belum mendukung varian atau opsi tambahan.",
                    ]);
                }

                if (!$product->consignmentPartner || !$product->consignmentPartner->is_active) {
                    throw ValidationException::withMessages([
                        'product_id' => "Mitra produk {$product->name} tidak aktif atau tidak ditemukan.",
                    ]);
                }

                if ($product->partner_price === null) {
                    throw ValidationException::withMessages([
                        'product_id' => "Harga mitra produk {$product->name} belum ditentukan.",
                    ]);
                }

                $partnerUnitPrice = round((float) $product->partner_price, 2);

                if ($unitPrice < $partnerUnitPrice) {
                    throw ValidationException::withMessages([
                        'product_id' => "Harga jual {$product->name} tidak boleh lebih rendah dari harga mitra.",
                    ]);
                }

                $consignmentData = [
                    'consignment_partner_id' => $product->consignment_partner_id,
                    'consignment_partner_name' => $product->consignmentPartner->name,
                    'partner_unit_price' => $partnerUnitPrice,
                    'partner_amount' => round($partnerUnitPrice * $quantity, 2),
                    'cafe_profit' => round(
                        ($unitPrice - $partnerUnitPrice) * $quantity,
                        2
                    ),
                ];
            }
            $lockedOrder->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'product_name' => $product->name,
                'variant_name' => $variant?->name,
                'selected_options' => $selectedOptions->map(fn ($option) => [
                    'id' => $option->id,
                    'name' => $option->name,
                    'price_adjustment' => (float) $option->price_adjustment,
                ])->values()->all(),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $itemSubtotal,
                'notes' => $validated['notes'] ?? null,
                'consignment_partner_id' => $consignmentData['consignment_partner_id'],
                'consignment_partner_name' => $consignmentData['consignment_partner_name'],
                'partner_unit_price' => $consignmentData['partner_unit_price'],
                'partner_amount' => $consignmentData['partner_amount'],
                'cafe_profit' => $consignmentData['cafe_profit'],
            ]);

            $this->recalculateOrderTotals($lockedOrder);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan ke pesanan.',
            'redirect' => $redirect,
        ]);
    }

    
    
    public function payOrder(Request $request,Order $order,StockMovementService $stockMovementService
    ) {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:cash,transfer,qris'],
            'amount_received' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use (
            $validated,
            $order,
            $stockMovementService
        ) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'open') {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan ini sudah dibayar atau tidak dapat dibayar.',
                ]);
            }

            $items = $lockedOrder->items()
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan kosong tidak dapat dibayar.',
                ]);
            }

            $total = round((float) $lockedOrder->total, 2);
            $received = round((float) $validated['amount_received'], 2);

            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'order' => 'Total pesanan harus lebih dari nol.',
                ]);
            }

            if ($validated['payment_method'] === 'cash') {
                if ($received < $total) {
                    throw ValidationException::withMessages([
                        'amount_received' => 'Uang yang diterima kurang dari total tagihan.',
                    ]);
                }
            } else {
                if (abs($received - $total) > 0.001) {
                    throw ValidationException::withMessages([
                        'amount_received' => 'Nominal transfer atau QRIS harus sama dengan total tagihan.',
                    ]);
                }
            }


            // Proses stok semua produk yang mengaktifkan track_stock.
            foreach ($items as $item) {
                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->first();

                // Produk konsinyasi harus tetap memiliki produk dan snapshot.
                if ($item->consignment_partner_id !== null) {
                    if (
                        !$product ||
                        $product->product_type !== 'consignment'
                    ) {
                        throw ValidationException::withMessages([
                            'order' => "Produk titipan {$item->product_name} tidak ditemukan atau jenis produknya telah berubah.",
                        ]);
                    }

                    if ($item->partner_unit_price === null) {
                        throw ValidationException::withMessages([
                            'order' => "Snapshot harga mitra untuk {$item->product_name} tidak tersedia.",
                        ]);
                    }
                }

                // Produk lama yang sudah tidak terhubung ke produk
                // tidak diproses stoknya, kecuali produk konsinyasi
                // yang sudah ditolak di validasi atas.
                if (!$product) {
                    continue;
                }

                // Produk tanpa pelacakan stok tidak mengurangi stok.
                if (!$product->track_stock) {
                    continue;
                }

                $stockMovementService->out(
                    product: $product,
                    qty: (int) $item->quantity,
                    referenceType: 'pos_sale',
                    referenceId: $lockedOrder->id,
                    note: "Penjualan POS {$lockedOrder->order_number}; order item #{$item->id}",
                    userId: auth()->id()
                );
            }


            Payment::create([
                'order_id' => $lockedOrder->id,
                'payment_number' => 'PAY-' . now()->format('YmdHis')
                    . '-' . Str::upper(Str::random(5)),
                'payment_method' => $validated['payment_method'],
                'paid_amount' => $total,
                'amount_received' => $received,
                'change_amount' => $validated['payment_method'] === 'cash'
                    ? round($received - $total, 2)
                    : 0,
                'paid_at' => now(),
            ]);

            $lockedOrder->update([
                'status' => 'paid',
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran berhasil disimpan.',
            'redirect' => route('pos.orders.show', $order->id),
        ]);
    }


    public function receipt(Order $order)
    {
        $order->load(['meja','items','payment',]);

        if ($order->status !== 'paid' || !$order->payment) {
            abort(404, 'Struk pembayaran tidak ditemukan.');
        }

        return view('pos.orders.receipt', compact('order'));
    }
}
