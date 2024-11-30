<?php

use App\Http\Controllers\api\CartController;
use App\Http\Controllers\api\InvoiceController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\SupplierController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\WorkspaceController;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */


Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index');
    Route::post('/user', 'store');
    Route::post('/user/login', 'loginUser');
    Route::get('/user/{id}', 'show');
    Route::post('/user/{id}', 'update');
    Route::delete('/user/{id}', 'destroy');
    Route::get('/user/{id}/workspace', 'getWorkSpaces');
    Route::post('/user/{id}/updatePassword', 'updatePassword');
    Route::post('/invitationRegister', 'registerInvitedUser');
    Route::post('/inviteUser', 'inviteUserToWorkspce');
    Route::post('/removeUser', 'removeUserFromWorkspace');
});

Route::controller(WorkspaceController::class)->group(function () {
    Route::get('/workspaces', 'index');
    Route::get('/workspace/{id}', 'show');
    Route::get('/workspace/{id}/users', 'getWorkspaceUsers');
    Route::post('/workspace/{id}', 'update');
    Route::delete('/workspace/{id}', 'destroy');
    Route::get('/workspace/{id}/products', 'getProductsByWorkspace');
    Route::get('/workspace/{id}/cart', 'getCartByWorkspaceId');
    Route::get('/workspace/{id}/invoices', 'getWorkspaceInvoices');
});

Route::controller(SupplierController::class)->group(function () {
    Route::get('/suppliers', 'index');
    Route::post('/supplier', 'store');
    Route::get('/supplier/{id}', 'show');
    Route::get('/supplierLtd/{id}', 'getLimitedSuppliers');
    Route::post('/supplier/{id}', 'update');
    Route::delete('/supplier/{id}', 'destroy');
    Route::get('/suppliers/{workspace_id}', 'getByWorkspaceId');
    Route::get('/suppliers/{id}/products', 'getProductsBySupplierID');
    Route::get('/supplier/{id}/orderMethod', 'getSupplierOrderMethod');
});

Route::controller(ProductController::class)->group(function () {
    Route::get('/products', 'index');
    Route::post('/product', 'store');
    Route::get('/product/{id}', 'show');
    Route::post('/product/{id}', 'update');
    Route::delete('/product/{id}', 'destroy');
});


Route::controller(CartController::class)->group(function () {
    Route::get('/carts', 'index');
    Route::get('/cart/{id}', 'show');
    Route::post('/cart/{id}/addProduct', 'addProductToCart');
    Route::post('/cart/{id}/removeProduct', 'removeProductFromCart');
    Route::post('/cart/{cartId}/updateProductQuantity', 'updateProductQuantity');
    Route::delete('/cart/{id}', 'destroy');
    Route::put('/cart/{id}', 'update');
});

Route::controller(OrderController::class)->group(function () {
    Route::post('/order/{cartId}/makeorder', 'makeOrder');
    Route::get('/order/{id}', 'show');
    Route::get('/{workspaceId}/orders', 'getWorkspaceOrders');
    Route::get('/order/{orderId}/{supplierId}', 'getOrderProducts');
    Route::post('/sendorder', 'sendOrderEmail');
    Route::post('/sendSMS', 'sendOrderSMS');
    Route::get('/orders/{workspaceId}/spendOnMonth', 'getTotalPriceForCurrentMonth');
    Route::get('/orders/{workspaceId}/monthlyRevenue', 'getMonthlyRevenueByWorkspace');
    Route::get('/productQuantities/{workspaceId}', 'productQuantitiesByMonth');
    Route::post('/order/{orderId}/updateStatus', 'updateOrderStatus');
});

Route::controller(InvoiceController::class)->group(function () {
    Route::get('/invoice/{orderId}', 'show');
    Route::delete('/invoice/{invoiceId}', 'destroy');
    Route::get('/invoice/{orderId}/{supplierId}', 'getOrderProducts');
    Route::post('/invoices/upload-pdf', 'uploadPDF');
    Route::get('/invoices/{workspaceId}/totalSize', 'getTotalSizeByWorkspaceId');
});
