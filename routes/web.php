<?php

use App\Http\Controllers\UserController;
use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvoiceController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

// Route Middleware
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Route Units
    Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    Route::get('/units/{id}/edit', [UnitController::class, 'edit'])->name('units.edit');
    Route::post('/units/store', [UnitController::class, 'store'])->name('units.store');
    Route::put('/units/{id}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('/units/{id}', [UnitController::class, 'destroy'])->name('units.destroy');

    // Route products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/add', [ProductController::class, 'create'])->name('products.add');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::get('/products/{id}/show', [ProductController::class,'show'])->name('products.show');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/logs', [ProductController::class, 'showLog'])->name('products.logs');


    //  Route generate unique code
    Route::get('/generate-product-code', function() {
        return response()->json(['code' => Product::generateUniqueCode()]);   
    });

    // Route generate invoice number
    Route::get('/generate-invoice-code', function() {
        return response()->json(['invoice_no' => Invoice::generateInvoiceNumber()]);
    });
    
    // Route suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/add', [SupplierController::class, 'create'])->name('suppliers.add');
    Route::get('/suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::get('/suppliers/{id}/show', [SupplierController::class,'show'])->name('suppliers.show');
    Route::post('/suppliers/store', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{id}/update', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{id}/destroy', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::get('/suppliers/logs', [SupplierController::class, 'showLog'])->name('suppliers.logs');
    
    // Route Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}/show', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/add', [PaymentController::class, 'create'])->name('payments.add');
    Route::post('/payments/store', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/process', [PaymentController::class, 'processPayment'])->name('payments.process');
    Route::get('/payments/logs', [PaymentController::class, 'showLog'])->name('payments.logs');
    Route::get('/get-warehouse-product', [ProductController::class, 'getWarehouseProduct'])->name('get-warehouse-product');
    Route::get('/get-product-detail/{id}', [ProductController::class, 'getProductDetail'])->name('get-product-detail');

    // Route Payments status
    Route::post('/payments/update-status/{id}', [PaymentController::class, 'updateStatus'])->name('payments.update-status');
    
    // Ambil data product dari supplier
    Route::post('/payments/get-products', [PaymentController::class, 'getProductsBySupplier'])->name('payments.getProductsBySupplier');
    
    // Route warehouses
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('/warehouses/{id}/show', [WarehouseController::class, 'show'])->name('warehouses.show');
    Route::put('/warehouses/{id}', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::get('/warehouses/logs', [WarehouseController::class, 'showLog'])->name('warehouses.logs');
    
    // Route roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/{id}/show', [RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/add', [RoleController::class, 'create'])->name('roles.add');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::post('/roles/store', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{id}/update', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}/destroy', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Route invoice
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('/invoices/{id}/show', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('/invoices/add', [InvoiceController::class, 'create'])->name('invoice.add');
    Route::post('/invoices/store', [InvoiceController::class, 'store'])->name('invoice.store');
    Route::get('/get-product-stock/{product_id}', [InvoiceController::class, 'getProductStock']);
    Route::get('/search-products', [InvoiceController::class, 'searchProducts']);
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'downloadPdf'])->name('invoice.download');


    // Route permissions
    Route::resource('permissions', PermissionController::class);
    
    //Route akses role
    Route::get('/assign-role/{userId}', [UserController::class, 'assignRoleToUser']);

    // Route user
    Route::resource('users', UserController::class);
    
    Route::get('/about', function () {
        return view('about');
    })->name('about');
});