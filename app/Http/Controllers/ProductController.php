<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ConsignmentPartner;
use App\Services\StockMovementService;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $products = Product::with([
                'category',
                'variants',
                'optionGroups.options',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('barcode', 'like', '%' . $search . '%');
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact(
            'products',
            'categories',
            'search',
            'categoryId'
        ));
    }



  
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        $optionGroups = OptionGroup::query()
            ->with([
                'options' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->whereHas('options', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        return view('products.create', compact(
            'categories',
            'optionGroups'
        ));
    }





    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],

            'hpp' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],

            'stock' => ['nullable', 'integer', 'min:0'],
            'track_stock' => ['nullable', 'boolean'],

            'option_group_ids' => ['nullable', 'array'],
            'option_group_ids.*' => [
                'integer',
                'distinct',
                'exists:option_groups,id',
            ],

            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required', 'string', 'max:100'],
            'variants.*.hpp' => ['required', 'numeric', 'min:0'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ]);

        $validated['barcode'] = $this->generateBarcode();
        $validated['is_active'] = true;
        $validated['track_stock'] = $request->boolean('track_stock');

        $validated['stock'] = $validated['track_stock']
            ? (int) ($validated['stock'] ?? 0)
            : 0;

        $variants = $validated['variants'] ?? [];
        $optionGroupIds = $validated['option_group_ids'] ?? [];

        unset(
            $validated['variants'],
            $validated['option_group_ids']
        );

        return DB::transaction(function () use (
            $validated,
            $variants,
            $optionGroupIds
        ) {
            $product = Product::create($validated);

            // Simpan varian produk.
            $this->syncVariants($product, [
                'variants' => $variants,
            ]);

            // Hubungkan kelompok pilihan dengan produk.
            $this->syncOptionGroups($product, [
                'option_group_ids' => $optionGroupIds,
            ]);

            $product->load([
                'category',
                'variants',
                'optionGroups.options',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Produk berhasil ditambahkan.',
                'redirect' => route('products.index'),
                'data' => $product,
            ], 201);
        });
    }


    public function edit(Product $product)
    {
        $product->load([
            'category',
            'variants',
            'optionGroups.options',
        ]);

        $categories = Category::orderBy('name')->get();

        $optionGroups = OptionGroup::query()
            ->with([
                'options' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->whereHas('options', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        return view('products.edit', compact(
            'product',
            'categories',
            'optionGroups'
        ));
    }



 
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'hpp' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'track_stock' => ['nullable', 'boolean'],
            'is_active' => ['required', 'boolean'],

            'option_group_ids' => ['nullable', 'array'],
            'option_group_ids.*' => [
                'integer',
                'distinct',
                'exists:option_groups,id',
            ],

            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.name' => ['required', 'string', 'max:100'],
            'variants.*.hpp' => ['required', 'numeric', 'min:0'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ]);

        $variants = $validated['variants'] ?? [];
        $optionGroupIds = $validated['option_group_ids'] ?? [];

        // Pastikan varian yang diedit benar-benar milik produk ini.
        foreach ($variants as $variantData) {
            if (!empty($variantData['id'])) {
                $belongsToProduct = $product->variants()
                    ->whereKey($variantData['id'])
                    ->exists();

                if (!$belongsToProduct) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Varian tidak valid untuk produk ini.',
                    ], 422);
                }
            }
        }

        unset(
            $validated['variants'],
            $validated['option_group_ids']
        );

        // Stok tidak boleh diubah melalui halaman edit.
        unset($validated['stock']);

        $validated['track_stock'] = $request->boolean('track_stock');

        return DB::transaction(function () use (
            $product,
            $validated,
            $variants,
            $optionGroupIds
        ) {
            $product->update($validated);

            $this->syncVariants($product, [
                'variants' => $variants,
            ]);

            $this->syncOptionGroups($product, [
                'option_group_ids' => $optionGroupIds,
            ]);

            $product->load([
                'category',
                'variants',
                'optionGroups.options',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Produk berhasil diperbarui.',
                'redirect' => route('products.index'),
                'data' => $product,
            ]);
        });
    }


    public function destroy(Product $product)
    {
        try {
            $product->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Produk berhasil dihapus.',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak dapat dihapus karena sudah digunakan dalam transaksi.',
            ], 409);
        }
    }

    private function validateProduct(
        Request $request,
        ?Product $product = null
    ): array {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'hpp' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'track_stock' => ['sometimes', 'boolean'],

            'variants' => ['sometimes', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => [
                'required',
                'string',
                'max:100',
            ],
            'variants.*.hpp' => ['required', 'numeric', 'min:0'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.is_active' => ['sometimes', 'boolean'],

            'option_group_ids' => ['sometimes', 'array'],
            'option_group_ids.*' => [
                'integer',
                'distinct',
                'exists:option_groups,id',
            ],
        ];

        if ($product) {
            $rules['is_active'] = ['required', 'boolean'];
        }

        $validated = $request->validate($rules);

        if (isset($validated['variants'])) {
            $names = array_map(
                fn ($variant) => mb_strtolower(trim($variant['name'])),
                $validated['variants']
            );

            if (count($names) !== count(array_unique($names))) {
                throw ValidationException::withMessages([
                    'variants' => 'Nama varian tidak boleh duplikat.',
                ]);
            }

            if ($product) {
                foreach ($validated['variants'] as $variant) {
                    if (!empty($variant['id'])) {
                        $belongsToProduct = $product->variants()
                            ->whereKey($variant['id'])
                            ->exists();

                        if (!$belongsToProduct) {
                            throw ValidationException::withMessages([
                                'variants' => 'Varian tidak valid untuk produk ini.',
                            ]);
                        }
                    }
                }
            }
        }

        return $validated;
    }


    private function syncVariants(Product $product, array $data): void
    {
        $variants = $data['variants'] ?? [];
        $keptIds = [];

        foreach ($variants as $variantData) {
            $variantId = $variantData['id'] ?? null;

            $attributes = [
                'name' => $variantData['name'],
                'hpp' => $variantData['hpp'],
                'price' => $variantData['price'],
                'is_active' => (bool) ($variantData['is_active'] ?? false),
            ];

            if ($variantId) {
                // Update varian yang sudah ada.
                $variant = $product->variants()
                    ->whereKey($variantId)
                    ->firstOrFail();

                $variant->update($attributes);
                $keptIds[] = $variant->id;
            } else {
                // Buat varian baru.
                $variant = $product->variants()->create($attributes);
                $keptIds[] = $variant->id;
            }
        }

        // Varian yang tidak dikirim dari form dinonaktifkan,
        // bukan dihapus, agar riwayat transaksi tetap aman.
        $product->variants()
            ->whereNotIn('id', $keptIds ?: [0])
            ->update(['is_active' => false]);
    }


    private function syncOptionGroups(
        Product $product,
        array $validated
    ): void {
        $product->optionGroups()->sync(
            $validated['option_group_ids'] ?? []
        );
    }

    private function generateBarcode(): string
    {
        do {
            $barcode = (string) random_int(10000, 99999);
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    public function consignmentIndex(Request $request)
    {
        $search = $request->input('search');

        $products = Product::with(['consignmentPartner', 'category'])
            ->where('product_type', 'consignment')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.consignment.index', compact('products', 'search'));
    }

    public function createConsignment()
    {
        $categories = Category::orderBy('name')->get();

        $partners = ConsignmentPartner::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('products.consignment.form', [
            'product' => new Product(),
            'categories' => $categories,
            'partners' => $partners,
            'pageTitle' => 'Tambah Produk Titipan',
        ]);
    }

    public function storeConsignment(Request $request,StockMovementService $stockMovementService) 
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'consignment_partner_id' => [
                'required',
                'integer',
                'exists:consignment_partners,id',
            ],
            'partner_price' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        if ((float) $validated['price'] < (float) $validated['partner_price']) {
            throw ValidationException::withMessages([
                'price' => 'Harga jual tidak boleh lebih rendah dari harga mitra.',
            ]);
        }

        $partner = ConsignmentPartner::query()
            ->whereKey($validated['consignment_partner_id'])
            ->where('is_active', true)
            ->first();

        if (!$partner) {
            throw ValidationException::withMessages([
                'consignment_partner_id' => 'Mitra tidak aktif atau tidak ditemukan.',
            ]);
        }

        $initialStock = (int) $validated['stock'];

        DB::transaction(function () use (
            $validated,
            $initialStock,
            $stockMovementService
        ) {
            $product = Product::create([
                'barcode' => $this->generateBarcode(),
                'name' => $validated['name'],
                'category_id' => $validated['category_id'] ?? null,
                'hpp' => $validated['partner_price'],
                'price' => $validated['price'],
                'stock' => 0,
                'track_stock' => true,
                'is_active' => true,
                'product_type' => 'consignment',
                'consignment_partner_id' => $validated['consignment_partner_id'],
                'partner_price' => $validated['partner_price'],
            ]);

            if ($initialStock > 0) {
                $stockMovementService->in(
                    product: $product,
                    qty: $initialStock,
                    referenceType: 'consignment_stock_in',
                    referenceId: $product->id,
                    note: 'Stok awal produk titipan'
                );
            }
        });

        return redirect()
            ->route('products.consignment.index')
            ->with('success', 'Produk titipan berhasil ditambahkan.');
    }

    public function editConsignment(Product $product)
    {
        abort_unless($product->product_type === 'consignment', 404);

        $categories = Category::orderBy('name')->get();

        $partners = ConsignmentPartner::query()
            ->where(function ($query) use ($product) {
                $query->where('is_active', true)
                    ->orWhereKey($product->consignment_partner_id);
            })
            ->orderBy('name')
            ->get();

        return view('products.consignment.form', [
            'product' => $product,
            'categories' => $categories,
            'partners' => $partners,
            'pageTitle' => 'Edit Produk Titipan',
        ]);
    }

    public function updateConsignment(Request $request,Product $product) 
    {
        abort_unless($product->product_type === 'consignment', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'consignment_partner_id' => [
                'required',
                'integer',
                'exists:consignment_partners,id',
            ],
            'partner_price' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ((float) $validated['price'] < (float) $validated['partner_price']) {
            throw ValidationException::withMessages([
                'price' => 'Harga jual tidak boleh lebih rendah dari harga mitra.',
            ]);
        }

        $partner = ConsignmentPartner::findOrFail(
            $validated['consignment_partner_id']
        );

        if (
            !$partner->is_active
            && (int) $product->consignment_partner_id !== (int) $partner->id
        ) {
            throw ValidationException::withMessages([
                'consignment_partner_id' => 'Mitra pengganti harus berstatus aktif.',
            ]);
        }

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'consignment_partner_id' => $partner->id,
            'partner_price' => $validated['partner_price'],
            'hpp' => $validated['partner_price'],
            'price' => $validated['price'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()
            ->route('products.consignment.index')
            ->with('success', 'Produk titipan berhasil diperbarui.');
    }

    public function createConsignmentStockIn(Product $product)
    {
        abort_unless($product->product_type === 'consignment', 404);

        return view('products.consignment.stock-in', compact('product'));
    }

    public function storeConsignmentStockIn(Request $request,Product $product,StockMovementService $stockMovementService) 
    {
        abort_unless($product->product_type === 'consignment', 404);

        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use (
            $product,
            $validated,
            $stockMovementService
        ) {
            $stockMovementService->in(
                product: $product,
                qty: (int) $validated['qty'],
                referenceType: 'consignment_stock_in',
                referenceId: $product->id,
                note: $validated['note'] ?? 'Stok masuk produk titipan',
                userId: auth()->id()
            );
        });

        return redirect()
            ->route('products.consignment.index')
            ->with(
                'success',
                'Stok produk titipan berhasil ditambahkan.'
            );
    }
}
