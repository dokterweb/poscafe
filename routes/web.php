<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConsignmentPartnerController;
use App\Http\Controllers\ConsignmentReportController;
use App\Http\Controllers\ConsignmentReturnController;
use App\Http\Controllers\ConsignmentSettlementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\OptionGroupController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('login', [AuthController::class, 'index'])->name('login');
Route::post('login', [AuthController::class, 'handleLogin']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
// FORM GANTI PASSWORD
Route::get('/password/change', [AuthController::class, 'changePasswordForm'])->middleware('auth')->name('password.change');
// PROSES UPDATE PASSWORD
Route::post('/password/update', [AuthController::class, 'updatePassword'])->middleware('auth')->name('password.update');

// Route untuk Admin
Route::middleware(['auth', 'role:admin'])->get('/admin/dashboard', [DashboardController::class, 'indexadmin'])->name('admin.dashboard');

// Route untuk Siswa
Route::middleware(['auth', 'role:kasir'])->get('/kasir/dashboard', [DashboardController::class, 'indexkasir'])->name('kasir.dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup', [BackupController::class, 'create'])->name('backup.create');
    Route::get('/backup/{filename}/download', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/{filename}', [BackupController::class, 'destroy'])->name('backup.destroy');

});

Route::get('/categories',[CategoryController::class, 'index'])->middleware('role:admin')->name('categories');
Route::post('categories/store', [CategoryController::class, 'store'])->middleware('role:admin')->name('categories.store');
Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->middleware('role:admin')->name('categories.edit');
Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('role:admin')->name('categories.update');
Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('role:admin')->name('categories.destroy');

Route::get('/suppliers',[SupplierController::class, 'index'])->middleware('role:admin')->name('suppliers');
Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('role:admin')->name('suppliers.store');
Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->middleware('role:admin')->name('suppliers.edit');
Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('role:admin')->name('suppliers.update');
Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('role:admin')->name('suppliers.destroy');

Route::middleware('role:admin')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::get('/products/create', [ProductController::class, 'create'])
        ->name('products.create');

    Route::post('/products', [ProductController::class, 'store'])
        ->name('products.store');

    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->name('products.edit');

    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->name('products.update');

    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->name('products.destroy');
});

Route::prefix('products/consignment')
    ->name('products.consignment.')
    ->group(function () {
        Route::get('/', [ProductController::class, 'consignmentIndex'])->name('index');
        Route::get('/create', [ProductController::class, 'createConsignment'])->name('create');
        Route::post('/', [ProductController::class, 'storeConsignment'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'editConsignment'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'updateConsignment'])->name('update');
         Route::get('/{product}/stock-in',[ProductController::class, 'createConsignmentStockIn'])
        ->name('stock-in.create');
        Route::post('/{product}/stock-in',[ProductController::class, 'storeConsignmentStockIn'])
        ->name('stock-in.store');
    });
       
    
    
Route::get('/customers', [CustomerController::class, 'index'])->middleware('role:admin')->name('customers.index');
Route::post('/customers', [CustomerController::class, 'store'])->middleware('role:admin')->name('customers.store');
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('role:admin')->name('customers.edit');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('role:admin')->name('customers.update');
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('role:admin')->name('customers.destroy');

Route::get('/sales', [SaleController::class, 'index'])->middleware('role:admin')->name('sales.index');
Route::get('/sales/create', [SaleController::class, 'create'])->middleware('role:admin')->name('sales.create');
Route::get('/sales/search-customers', [SaleController::class, 'searchCustomers'])->middleware('role:admin')->name('sales.search-customers');
Route::get('/sales/search-products', [SaleController::class, 'searchProducts'])->middleware('role:admin')->name('sales.search-products');
Route::post('/sales', [SaleController::class, 'store'])->middleware('role:admin')->name('sales.store');
Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])->middleware('role:admin')->name('sales.edit');
Route::put('/sales/{sale}', [SaleController::class, 'update'])->middleware('role:admin')->name('sales.update');
Route::get('/sales/{sale}', [SaleController::class, 'show'])->middleware('role:admin')->name('sales.show');
Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->middleware('role:admin')->name('sales.destroy');
Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->middleware('role:admin')->name('sales.print');

Route::get('/purchases', [PurchaseController::class, 'index'])->middleware('role:admin')->name('purchases.index');
Route::get('/purchases/create', [PurchaseController::class, 'create'])->middleware('role:admin')->name('purchases.create');
Route::post('/purchases', [PurchaseController::class, 'store'])->middleware('role:admin')->name('purchases.store');
Route::get('/purchases/search-suppliers', [PurchaseController::class, 'searchSuppliers'])->middleware('role:admin')->name('purchases.searchSuppliers');
Route::get('/purchases/search-products', [PurchaseController::class, 'searchProducts'])->middleware('role:admin')->name('purchases.searchProducts');
Route::post('/purchases/products', [PurchaseController::class, 'storeProduct'])->middleware('role:admin')->name('purchases.storeProduct');
Route::get('/purchases/generate-product-barcode', [PurchaseController::class, 'generateProductBarcode'])->middleware('role:admin')->name('purchases.generateProductBarcode');
// REPORT HARUS SEBELUM /purchases/{purchase}
Route::get('/purchases/report', [PurchaseController::class, 'report'])->middleware('role:admin')->name('purchases.report');
// DETAIL HARUS SETELAH /create
Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->middleware('role:admin')->name('purchases.show');
// DELETE
Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->middleware('role:admin')->name('purchases.destroy');

Route::get('/stock-movements', [StockMovementController::class, 'index'])->middleware('role:admin')->name('stock-movements.index');

Route::get('/stock-adjustments',[StockAdjustmentController::class, 'index'])->middleware('role:admin')->name('stock-adjustments.index');
Route::get('/stock-adjustments/create',[StockAdjustmentController::class, 'create'])->middleware('role:admin')->name('stock-adjustments.create');
Route::post('/stock-adjustments',[StockAdjustmentController::class, 'store'])->middleware('role:admin')->name('stock-adjustments.store');
Route::get('/stock-adjustments/search-products',[StockAdjustmentController::class, 'searchProducts'])->middleware('role:admin')->name('stock-adjustments.search-products');

Route::get('/option-groups/{option_group}/data',[OptionGroupController::class, 'editData'])->name('option-groups.data');
Route::resource('option-groups', OptionGroupController::class)->only(['index', 'store', 'update', 'destroy']);

Route::middleware('role:admin')->group(function () {
    Route::get('/mejas', [MejaController::class, 'index'])
        ->name('mejas.index');

    Route::get('/mejas/create', [MejaController::class, 'create'])
        ->name('mejas.create');

    Route::post('/mejas', [MejaController::class, 'store'])
        ->name('mejas.store');

    Route::get('/mejas/{meja}/edit', [MejaController::class, 'edit'])
        ->name('mejas.edit');

    Route::put('/mejas/{meja}', [MejaController::class, 'update'])
        ->name('mejas.update');

    Route::delete('/mejas/{meja}', [MejaController::class, 'destroy'])
        ->name('mejas.destroy');
});

Route::middleware('role:admin')->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/orders', [PosController::class, 'store'])->name('pos.orders.store');
    Route::get('/pos/orders/{order}', [PosController::class, 'showOrder'])->name('pos.orders.show');
    Route::get('/pos/mejas', [PosController::class, 'mejaIndex'])->name('pos.mejas.index');
    Route::patch('/pos/orders/{order}/items/{item}', [PosController::class,'updateOrderItem',])->name('pos.orders.items.update');
    Route::delete('/pos/orders/{order}/items/{item}', [PosController::class,'destroyOrderItem',])->name('pos.orders.items.destroy');
    Route::post('/pos/orders/{order}/items',[PosController::class, 'addOrderItem'])->name('pos.orders.items.store');
    Route::post('/pos/orders/{order}/pay',[PosController::class, 'payOrder'])->name('pos.orders.pay');
    Route::get('/pos/orders/{order}/receipt', [PosController::class, 'receipt'])->name('pos.orders.receipt');
});


Route::get('/pos/laporan-penjualan', [LaporanPenjualanController::class,'index'])->name('pos.laporan-penjualan.index');
Route::get('/pos/laporan-penjualan/export', [
    LaporanPenjualanController::class,
    'export',
])->name('pos.laporan-penjualan.export');

Route::resource('pos/consignment-partners',ConsignmentPartnerController::class)
    ->except(['show', 'destroy'])->parameters(['consignment-partners' => 'consignment_partner',])
    ->names('pos.consignment-partners');

Route::get('/consignment-reports',[ConsignmentReportController::class, 'index'])->name('consignment-reports.index');
Route::prefix('consignment-settlements')
    ->name('consignment-settlements.')
    ->controller(ConsignmentSettlementController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{settlement}', 'show')->whereNumber('settlement')->name('show');
        Route::post('/{settlement}/pay', 'pay')->whereNumber('settlement')->name('pay');
    });

Route::prefix('consignment-returns')
    ->name('consignment-returns.')
    ->controller(ConsignmentReturnController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{consignmentReturn}', 'show')->whereNumber('consignmentReturn')->name('show');
        Route::post('/{consignmentReturn}/process', 'process')->whereNumber('consignmentReturn')->name('process');
    });