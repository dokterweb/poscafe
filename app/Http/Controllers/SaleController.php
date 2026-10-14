<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer']);

        //SEARCH
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name','like','%' . $search . '%');
                    });
            });
        }

        //TANGGAL DARI
        if ($request->filled('date_from')) {
            $query->whereDate('date','>=',$request->date_from);
        }

        //TANGGAL SAMPAI
        
        if ($request->filled('date_to')) {
            $query->whereDate('date','<=',$request->date_to);
        }

        //STATUS PEMBAYARAN
        if (
            $request->filled('payment_status') &&
            $request->payment_status !== 'all'
        ) {

            $query->where('payment_status',$request->payment_status);
        }

        //ORDER
        $sales = $query->latest('date')->latest('id')->paginate(20)->withQueryString();
        $totalSales =(clone $query)->sum('final_price');
        $totalTransaction =(clone $query)->count();

        return view('sales.index',compact('sales','totalSales','totalTransaction'));
    }
    
    public function create()
    {
        return view('sales.create', [
            'sale'        => null,
            'initialCart' => [],
        ]);
    }

    public function searchCustomers(Request $request)
    {
        $search = $request->input('q');

        $customers = Customer::query()
            ->where('name', 'like', '%' . $search . '%')
            ->orderBy('name')->limit(10)
            ->get(['id','name','phone',]);

        return response()->json($customers);
    }

    public function searchProducts(Request $request)
    {
        $search = $request->input('q');

        $products = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where(function ($query) use ($search) {

                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');

            })
            ->orderBy('name')->limit(15)
            ->get(['id','barcode','name','price','stock',]);

        return response()->json($products);
    }

    public function store(Request $request,StockMovementService $stockMovementService) 
    {
        $validated = $request->validate([

            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'date' => [
                'required',
                'date',
            ],

            'payment_status' => [
                'required',
                'in:belum_lunas,lunas',
            ],

            'note' => [
                'nullable',
                'string',
            ],

            'cart' => [
                'required',
                'array',
                'min:1',
            ],

            'cart.*.id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'cart.*.qty' => [
                'required',
                'integer',
                'min:1',
            ],

            'cart.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'cart.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

        ]);


        try {

            $sale = DB::transaction(function () use (
                $validated,
                $stockMovementService
            ) {

                // ==================================================
                // GENERATE INVOICE
                // ==================================================


                $totalPrice = 0;

                $totalDiscount = 0;

                $totalHpp = 0;


                // ==================================================
                // CREATE SALE HEADER
                // ==================================================

                $sale = Sale::create([

                'invoice' =>
                    'TEMP-' . uniqid(),

                'customer_id' =>
                    $validated['customer_id'] ?? null,

                'jenis' =>
                    'toko',

                'total_hpp' =>
                    0,

                'total_price' =>
                    0,

                'discount' =>
                    0,

                'final_price' =>
                    0,

                'note' =>
                    $validated['note'] ?? null,

                'date' =>
                    $validated['date'],

                'user_id' =>
                    Auth::id(),

                'payment_status' =>
                    $validated['payment_status'],

            ]);

            $sale->update(['invoice' =>'INV' . str_pad($sale->id,5,'0',STR_PAD_LEFT),]);

                // ==================================================
                // PROCESS CART
                // ==================================================

                foreach (
                    $validated['cart']
                    as $cartItem
                ) {

                    // ==============================================
                    // FIND PRODUCT
                    // ==============================================

                    $product = Product::find(
                        $cartItem['id']
                    );


                    if (!$product) {

                        throw new \Exception(
                            'Produk tidak ditemukan.'
                        );

                    }


                    // ==============================================
                    // QTY
                    // ==============================================

                    $qty =
                        (int) $cartItem['qty'];


                    if ($qty <= 0) {

                        throw new \Exception(
                            'Qty produk tidak valid.'
                        );

                    }


                    // ==============================================
                    // PRICE
                    // ==============================================

                    $price =
                        (float) $cartItem['price'];


                    // ==============================================
                    // DISCOUNT
                    // ==============================================

                    $discount =
                        isset($cartItem['discount'])
                            ? (float) $cartItem['discount']
                            : 0;


                    // ==============================================
                    // CALCULATION
                    // ==============================================

                    $subtotal =
                        $price * $qty;


                    $discountValue =
                        $subtotal *
                        ($discount / 100);


                    $total =
                        $subtotal -
                        $discountValue;


                    // ==============================================
                    // HPP
                    // ==============================================

                    $detailHpp =
                        (float) $product->hpp *
                        $qty;


                    // ==============================================
                    // STOCK MOVEMENT
                    //
                    // Sale = stock OUT
                    // ==============================================

                    $stockMovementService->out(

                        product: $product,

                        qty: $qty,

                        referenceType: 'sale',

                        referenceId: $sale->id,

                        note:
                            'Penjualan barang ' .
                            $sale->invoice,

                        userId:
                            Auth::id()

                    );


                    // ==============================================
                    // SAVE SALE DETAIL
                    // ==============================================

                    SaleDetail::create([

                        'sale_id' =>
                            $sale->id,

                        'product_id' =>
                            $product->id,

                        'price' =>
                            $price,

                        'qty' =>
                            $qty,

                        'discount' =>
                            $discount,

                        'total' =>
                            $total,

                        'total_hpp' =>
                            $detailHpp,

                    ]);


                    // ==============================================
                    // ACCUMULATE
                    // ==============================================

                    $totalPrice +=
                        $subtotal;


                    $totalDiscount +=
                        $discountValue;


                    $totalHpp +=
                        $detailHpp;

                }


                // ==================================================
                // FINAL TOTAL
                // ==================================================

                $finalPrice =
                    $totalPrice -
                    $totalDiscount;


                // ==================================================
                // UPDATE SALE HEADER
                // ==================================================

                $sale->update([

                    'total_hpp' =>
                        $totalHpp,

                    'total_price' =>
                        $totalPrice,

                    'discount' =>
                        $totalDiscount,

                    'final_price' =>
                        $finalPrice,

                ]);


                return $sale;

            });


            // ======================================================
            // SUCCESS RESPONSE
            // ======================================================

            return response()->json([

                'status' =>
                    'success',

                'message' =>
                    'Transaksi berhasil disimpan.',

                'sale_id' =>
                    $sale->id,

                'invoice' =>
                    $sale->invoice,

                'redirect' =>
                    route('sales.create'),

            ]);

        }


        catch (\Throwable $e) {

            report($e);


            return response()->json([

                'status' =>
                    'error',

                'message' =>
                    $e->getMessage(),

            ], 422);

        }
    } 

    public function print(Sale $sale)
    {
        $sale->load(['customer','details.product',]);
        return view('sales.print', compact('sale'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer','details.product','user',]);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $sale->load(['customer','details.product',]);

        $initialCart = $sale->details->map(function ($detail) {
            return [
                'id'       => $detail->product_id,
                'barcode'  => $detail->product?->barcode,
                'name'     => $detail->product?->name,
                'price'    => (float) $detail->price,
                'stock'    => (int) ($detail->product?->stock ?? 0),
                'qty'      => (int) $detail->qty,
                'discount' => (float) $detail->discount,
            ];

        })->values();

        return view('sales.create', [
            'sale'        => $sale,
            'initialCart' => $initialCart,
        ]);
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable','exists:customers,id',],
            'date' => ['required','date',],
            'payment_status' => ['required','in:belum_lunas,lunas',],
            'note' => ['nullable','string',],
            'cart' => ['required','array','min:1',],
            'cart.*.id' => ['required','integer','exists:products,id',],
            'cart.*.price' => ['required','numeric','min:0',],
            'cart.*.qty' => ['required','integer','min:1',],
            'cart.*.discount' => ['required','numeric','min:0','max:100',],
        ]);

        try {
            $updatedSale = DB::transaction(function () use ($validated,$sale) {

                //LOCK SALE
                $sale = Sale::where('id', $sale->id)->lockForUpdate()->first();
                if (!$sale) {
                    throw ValidationException::withMessages([
                        'sale' => 'Transaksi tidak ditemukan.'
                    ]);
                }

                //DETAIL LAMA
                $oldDetails = SaleDetail::where('sale_id',$sale->id)->get();
                //NORMALISASI CART
                $cart = collect($validated['cart']);

                //CEK DUPLICATE PRODUCT
                if (
                    $cart->pluck('id')->unique()->count()
                    !==
                    $cart->count()
                ) {
                    throw ValidationException::withMessages([
                        'cart' => 'Produk yang sama tidak boleh muncul lebih dari satu kali.'
                    ]);
                }

                //ID PRODUCT
                $productIds = collect()
                    ->merge($oldDetails->pluck('product_id'))
                    ->merge($cart->pluck('id'))
                    ->unique()
                    ->values();

                //LOCK PRODUCT
                $products = Product::whereIn('id',$productIds)
                ->lockForUpdate()->get()->keyBy('id');

                /*
                | HITUNG STOCK YANG DAPAT DIGUNAKAN
                | Stock saat ini masih sudah dikurangi oleh transaksi lama.
                | Jadi:
                | stock tersedia untuk edit =
                | stock sekarang + qty lama
                */

                $oldQty = $oldDetails->groupBy('product_id')->map(function ($details) {
                        return $details->sum('qty');
                    });

                //VALIDASI PRODUCT BARU
                foreach ($cart as $item) {
                    $productId = $item['id'];
                    $product = $products->get($productId);
                    if (!$product) {
                        throw ValidationException::withMessages([
                            'cart' =>
                                'Produk tidak ditemukan.'
                        ]);
                    }

                    $stockAvailable =$product->stock+($oldQty[$productId] ?? 0);
                    if (
                        (int) $item['qty'] > $stockAvailable
                    ) {
                        throw ValidationException::withMessages([
                            'cart' =>'Stock produk "'. $product->name. '" tidak mencukupi. '. 'Stock tersedia: '. $stockAvailable
                        ]);
                    }
                }

                //KEMBALIKAN STOCK TRANSAKSI LAMA
                foreach ($oldDetails as $detail) {
                    $product = $products->get(
                        $detail->product_id
                    );

                    if ($product) {
                        $product->increment('stock',$detail->qty);
                    }
                }

                //HAPUS DETAIL LAMA
                SaleDetail::where('sale_id',$sale->id)->delete();

                //TOTAL
                $totalPrice = 0;
                $totalDiscount = 0;
                $totalHpp = 0;

                //INSERT DETAIL BARU
                foreach ($cart as $item) {
                    $product = $products->get($item['id']);
                    $price = (float) $item['price'];
                    $qty = (int) $item['qty'];
                    $discount = (float) $item['discount'];
                    //Subtotal sebelum discount
                    $subtotal =$price * $qty;
                    //Nilai discount
                    $discountValue =$subtotal *($discount / 100);
                    //Total detail
                    $total =$subtotal -$discountValue;
                    //HPP
                    $detailHpp = (float) $product->hpp * $qty;


                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' =>$product->id,
                        'price' =>$price,
                        'qty' =>$qty,
                        'discount' =>$discount,
                        'total' =>$total,
                        'total_hpp' => $detailHpp,
                    ]);

                    //Kurangi stock baru
                    $product->decrement('stock',$qty);

                    //Akumulasi
                    $totalPrice +=$subtotal;
                    $totalDiscount +=$discountValue;
                    $totalHpp += $detailHpp;
                }

                //FINAL PRICE
                $finalPrice =$totalPrice -$totalDiscount;

                //UPDATE HEADER
                $sale->update([
                    'customer_id' =>$validated['customer_id'] ?? null,
                    'total_hpp' =>$totalHpp,
                    'total_price' =>$totalPrice,
                    'discount' =>$totalDiscount,
                    'final_price' =>$finalPrice,
                    'date' =>$validated['date'],
                    'payment_status' =>$validated['payment_status'],
                    'note' =>$validated['note'] ?? null,
                ]);
                return $sale;
            });

            //RESPONSE
            return response()->json([
                'status' =>'success',
                'sale_id' =>$updatedSale->id,
                'invoice' =>$updatedSale->invoice,
                'redirect' => route('sales.show',$updatedSale),
                'message' => 'Transaksi berhasil diperbarui.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' =>'error',
                'message' =>collect($e->errors())->flatten()->first()
            ], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'status' =>'error',
                'message' =>'Terjadi kesalahan saat memperbarui transaksi.'
            ], 500);
        }
    }

    public function destroy(
    Sale $sale,
    StockMovementService $stockMovementService
) {
    try {

        DB::transaction(function () use (
            $sale,
            $stockMovementService
        ) {

            // ==================================================
            // LOAD SALE DETAILS + PRODUCT
            // ==================================================

            $sale->load([
                'details.product',
            ]);


            // ==================================================
            // VALIDASI DETAIL
            // ==================================================

            if ($sale->details->isEmpty()) {

                throw new \Exception(
                    'Transaksi penjualan tidak memiliki detail barang.'
                );

            }


            // ==================================================
            // RESTORE STOCK
            // ==================================================

            foreach ($sale->details as $detail) {

                $product = $detail->product;


                // ==================================================
                // PRODUCT TIDAK DITEMUKAN
                // ==================================================

                if (!$product) {

                    throw new \Exception(
                        "Produk dengan ID {$detail->product_id} tidak ditemukan."
                    );

                }


                // ==================================================
                // QTY
                // ==================================================

                $qty = (int) $detail->qty;


                if ($qty <= 0) {

                    throw new \Exception(
                        "Qty produk {$product->name} tidak valid."
                    );

                }


                // ==================================================
                // STOCK MOVEMENT
                //
                // Saat Sale:
                // stock - qty
                //
                // Saat Delete Sale:
                // stock + qty
                // ==================================================

                $stockMovementService->in(

                    product: $product,

                    qty: $qty,

                    referenceType: 'sale',

                    referenceId: $sale->id,

                    note:
                        'Pembatalan penjualan ' .
                        $sale->invoice,

                    userId:
                        auth()->id()

                );

            }


            // ==================================================
            // DELETE SALE DETAILS
            // ==================================================

            $sale->details()->delete();


            // ==================================================
            // DELETE SALE
            // ==================================================

            $sale->delete();

        });


        // ======================================================
        // SUCCESS
        // ======================================================

        return response()->json([

            'status' => 'success',

            'message' =>
                'Penjualan berhasil dihapus dan stok telah dikembalikan.',

        ], 200);


    } catch (\Throwable $e) {

        // ======================================================
        // LOG ERROR
        // ======================================================

        report($e);


        // ======================================================
        // ERROR
        // ======================================================

        return response()->json([

            'status' => 'error',

            'message' =>
                $e->getMessage(),

        ], 422);
    }
}

}