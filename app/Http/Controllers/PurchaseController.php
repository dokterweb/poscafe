<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StockMovementService;

class PurchaseController extends Controller
{
   public function index(Request $request)
    {
        $query = Purchase::with(['supplier','user',]);
        // ==========================================================
        // SEARCH
        // ==========================================================
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice', 'like', '%' . $search . '%')
                    ->orWhereHas('supplier', function ($supplier) use ($search) {
                        $supplier->where('name','like','%' . $search . '%');
                    });
            });
        }
        // ==========================================================
        // FILTER TANGGAL MULAI
        // ==========================================================
        if ($request->filled('date_from')) {
            $query->whereDate('date','>=',$request->date_from);
        }
        // ==========================================================
        // FILTER TANGGAL SAMPAI
        // ==========================================================
        if ($request->filled('date_to')) {
            $query->whereDate('date','<=',$request->date_to);
        }
        // ==========================================================
        // TOTAL PEMBELIAN HASIL FILTER
        // ==========================================================
        $totalPurchase =(clone $query)->sum('total');
        $totalTransaction =(clone $query)->count();
        // ==========================================================
        // PAGINATION
        // ==========================================================
        $purchases = $query->latest('date')->latest('id')->paginate(10)->withQueryString();

        return view('purchases.index',compact('purchases','totalPurchase','totalTransaction'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('purchases.create', compact('categories'));
    }

    public function searchSuppliers(Request $request)
    {
        $keyword = trim($request->get('q', ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        $suppliers = Supplier::query()
            ->where('name', 'like', "%{$keyword}%")
            ->orderBy('name')
            ->limit(10)
            ->get([
                'id',
                'name',
                'phone',
            ]);

        return response()->json($suppliers);
    }

    public function searchProducts(Request $request)
    {
        $keyword = trim($request->get('q', ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        $products = Product::query()
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('barcode', 'like', "%{$keyword}%");
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(10)
            ->get([
                'id',
                'barcode',
                'name',
                'hpp',
                'price',
                'stock',
            ]);

        return response()->json($products);
    }


    public function storeProduct(Request $request)
    {
        $validated = $request->validate([

            'barcode' => [
                'required',
                'string',
                'size:5',
                'unique:products,barcode',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'category_id' => [
                'required',
                'exists:categories,id',
            ],

            'hpp' => [
                'required',
                'numeric',
                'min:0',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

        ]);


        $product = Product::create([

            'barcode' => $validated['barcode'],

            'name' => $validated['name'],

            'category_id' => $validated['category_id'],

            'hpp' => $validated['hpp'],

            'price' => $validated['price'],

            // Stok belum bertambah.
            // Akan bertambah saat Purchase disimpan.
            'stock' => 0,

            'is_active' => true,

        ]);


        return response()->json([

            'success' => true,

            'message' => 'Produk berhasil ditambahkan.',

            'product' => [

                'id' => $product->id,

                'barcode' => $product->barcode,

                'name' => $product->name,

                'hpp' => $product->hpp,

                'price' => $product->price,

                'stock' => $product->stock,

            ],

        ]);
    }

    public function store(Request $request,StockMovementService $stockMovementService) 
    {
        // ==========================================================
        // VALIDATION
        // ==========================================================

        $validated = $request->validate([

            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],

            'date' => [
                'required',
                'date',
            ],

            'note' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'exists:products,id',
            ],

            'items.*.qty' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

        ]);


        DB::beginTransaction();

        try {

            // ======================================================
            // GENERATE INVOICE
            // ======================================================

            $invoice = 'PB-' . now()->format('YmdHis');


            // ======================================================
            // HITUNG TOTAL
            // ======================================================

            $grandTotal = 0;


            foreach ($validated['items'] as $item) {

                $qty =
                    (int) $item['qty'];

                $price =
                    (float) $item['price'];


                $grandTotal +=
                    $qty * $price;

            }


            // ======================================================
            // CREATE PURCHASE
            // ======================================================

            $purchase = Purchase::create([

                'invoice' =>
                    $invoice,

                'supplier_id' =>
                    $validated['supplier_id'],

                'date' =>
                    $validated['date'],

                'total' =>
                    $grandTotal,

                'note' =>
                    $validated['note'] ?? null,

                'user_id' =>
                    auth()->id(),

            ]);


            // ======================================================
            // CREATE DETAILS + STOCK MOVEMENT
            // ======================================================

            foreach ($validated['items'] as $item) {

                // ==================================================
                // LOCK PRODUCT
                // ==================================================

               $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception(
                        'Produk tidak ditemukan.'
                    );
                }


                $qty =
                    (int) $item['qty'];


                $price =
                    (float) $item['price'];


                $total =
                    $qty * $price;


                // ==================================================
                // PURCHASE DETAIL
                // ==================================================

                $purchase->details()->create([

                    'product_id' =>
                        $product->id,

                    'price' =>
                        $price,

                    'qty' =>
                        $qty,

                    'total' =>
                        $total,

                ]);


                // ==================================================
                // STOCK MOVEMENT
                // ==================================================

                $stockMovementService->in(

                    product: $product,

                    qty: $qty,

                    referenceType: 'purchase',

                    referenceId: $purchase->id,

                    note: 'Pembelian barang ' . $purchase->invoice,

                    userId: auth()->id()

                );


                // ==================================================
                // UPDATE HPP
                // ==================================================

                $product->hpp =
                    $price;

                $product->save();

            }


            // ======================================================
            // COMMIT
            // ======================================================

            DB::commit();


            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Pembelian berhasil disimpan.'
                );

        }


        catch (\Throwable $e) {

            DB::rollBack();


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Pembelian gagal disimpan: ' .
                    $e->getMessage()
                );

        }
    }
    public function generateProductBarcode()
    {
        do {
            $barcode = (string) random_int(10000, 99999);
        } while (
            Product::where('barcode', $barcode)->exists()
        );
        return response()->json([
            'success' => true,
            'barcode' => $barcode,
        ]);
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier','user','details.product',]);

        $totalQty = $purchase->details->sum('qty');

        return view('purchases.show',compact('purchase','totalQty'));
    }

    public function destroy(Purchase $purchase,StockMovementService $stockMovementService) 
    {
        DB::beginTransaction();

        try {

            // ======================================================
            // LOAD DETAILS
            // ======================================================

            $purchase->load([
                'details.product',
            ]);


            // ======================================================
            // VALIDASI DETAIL
            // ======================================================

            if ($purchase->details->isEmpty()) {

                throw new \Exception(
                    'Pembelian tidak memiliki detail barang.'
                );

            }


            // ======================================================
            // RESTORE / KEMBALIKAN STOCK
            //
            // Karena Purchase sebelumnya menambah stock,
            // maka ketika Purchase dihapus kita harus
            // mengurangi kembali stock tersebut.
            // ======================================================

            foreach ($purchase->details as $detail) {

                $product = $detail->product;


                // ==================================================
                // PRODUCT TIDAK DITEMUKAN
                // ==================================================

                if (!$product) {

                    throw new \Exception(
                        "Produk dengan ID {$detail->product_id} tidak ditemukan."
                    );

                }


                $qty = (int) $detail->qty;


                if ($qty <= 0) {

                    throw new \Exception(
                        "Qty produk {$product->name} tidak valid."
                    );

                }


                // ==================================================
                // STOCK MOVEMENT
                //
                // Purchase sebelumnya:
                // stock + qty
                //
                // Delete Purchase:
                // stock - qty
                // ==================================================

                $stockMovementService->out(

                    product: $product,

                    qty: $qty,

                    referenceType: 'purchase',

                    referenceId: $purchase->id,

                    note: 'Pembatalan pembelian ' . $purchase->invoice,

                    userId: auth()->id()

                );

            }


            // ======================================================
            // DELETE PURCHASE DETAILS
            // ======================================================

            $purchase->details()->delete();


            // ======================================================
            // DELETE PURCHASE
            // ======================================================

            $purchase->delete();


            // ======================================================
            // COMMIT
            // ======================================================

            DB::commit();


            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Pembelian berhasil dihapus dan stok telah dikembalikan.'
                );

        }


        catch (\Throwable $e) {

            DB::rollBack();


            return back()
                ->with(
                    'error',
                    'Pembelian gagal dihapus: ' .
                    $e->getMessage()
                );

        }
    }

    public function report(Request $request)
    {
        $query = Purchase::with(['supplier','details.product',]);
        // ==========================================================
        // DATE FROM
        // ==========================================================
        if ($request->filled('date_from')) {
            $query->whereDate('date','>=',$request->date_from);
        }
        // ==========================================================
        // DATE TO
        // ==========================================================
        if ($request->filled('date_to')) {
            $query->whereDate('date','<=',$request->date_to);
        }
        // ==========================================================
        // SUPPLIER
        // ==========================================================
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id',$request->supplier_id);
        }

        // ==========================================================
        // SEARCH INVOICE
        // ==========================================================
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('invoice','like','%' . $search . '%');
        }
        // ==========================================================
        // SUMMARY
        // ==========================================================
        $totalTransaction =(clone $query)->count();
        $totalPurchase =(clone $query)->sum('total');
        // ==========================================================
        // TOTAL QTY
        // ==========================================================
        $totalQty = (clone $query)->join('purchase_details','purchases.id','=','purchase_details.purchase_id')
                    ->sum('purchase_details.qty');

        // ==========================================================
        // PURCHASE LIST
        // ==========================================================
        $purchases = $query->latest('date')->latest('id')->paginate(15)->withQueryString();
        // ==========================================================
        // SUPPLIERS
        // ==========================================================
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.report',compact('purchases','suppliers','totalTransaction','totalPurchase','totalQty'));
    }
}
