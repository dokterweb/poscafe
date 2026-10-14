<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
   public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $products = Product::with('category')
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            'category_id' => [
                'nullable',
                'exists:categories,id'
            ],

            'hpp' => [
                'required',
                'numeric',
                'min:0'
            ],

            'price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'stock' => [
                'required',
                'integer',
                'min:0'
            ],
        ]);

        $validated['barcode'] = $this->generateBarcode();

        $validated['is_active'] = true;

        $product = Product::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan.',
            'data' => $product->load('category'),
        ]);
    }

    public function edit(Product $product)
    {
        return response()->json([
            'status' => 'success',
            'data' => $product->load('category'),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            'category_id' => [
                'nullable',
                'exists:categories,id'
            ],

            'hpp' => [
                'required',
                'numeric',
                'min:0'
            ],

            'price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'is_active' => [
                'required',
                'boolean'
            ],
        ]);

        $product->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diperbarui.',
            'data' => $product->fresh()->load('category'),
        ]);
    }

  public function destroy(Product $product)
    {
        try {

            $product->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Produk berhasil dihapus.',
            ]);

        } catch (\Illuminate\Database\QueryException $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Produk tidak dapat dihapus karena sudah digunakan dalam transaksi.',
            ], 409);
        }
    }

    private function generateBarcode(): string
    {
        do {
            $barcode = (string) random_int(
                10000,
                99999
            );
        } while (
            Product::where('barcode', $barcode)->exists()
        );

        return $barcode;
    }
}