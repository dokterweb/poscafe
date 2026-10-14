<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
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

Route::get('/products', [ProductController::class, 'index'])->middleware('role:admin')->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->middleware('role:admin')->name('products.store');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->middleware('role:admin')->name('products.edit');
Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('role:admin')->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('role:admin')->name('products.destroy');

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