<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventManagementController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\DiscountCodeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaystackWebhookController;

Route::post('/webhook/paystack', [PaystackWebhookController::class, 'handle']);

Route::prefix('v1')->group(function () {
    Route::get('/events', [EventController::class, 'apiIndex']);
    Route::get('/events/{event}', [EventController::class, 'apiShow']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/tickets/{ticket}/qr', [OrderController::class, 'qrCode']);
        Route::post('/orders/{order}/refund', [RefundController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'role:Admin|Event Manager|Box Office'])->group(function () {
        Route::post('/check-in', [OrderController::class, 'checkIn']);
    });

    Route::middleware(['auth:sanctum', 'role:Admin|Event Manager'])->group(function () {
        Route::get('/manage/events', [EventManagementController::class, 'index']);
        Route::post('/manage/events', [EventManagementController::class, 'store']);
        Route::post('/manage/events/{event}/ticket-types', [EventManagementController::class, 'storeTicketType']);
        Route::get('/manage/discount-codes', [DiscountCodeController::class, 'index']);
        Route::post('/manage/discount-codes', [DiscountCodeController::class, 'store']);
        Route::get('/manage/promoters', [PromoterController::class, 'index']);
        Route::post('/manage/promoters', [PromoterController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
        Route::get('/manage/staff', [StaffManagementController::class, 'index']);
        Route::post('/manage/staff', [StaffManagementController::class, 'store']);
        Route::patch('/manage/staff/{user}', [StaffManagementController::class, 'updateRole']);

        Route::get('/manage/refunds', [RefundController::class, 'index']);
        Route::post('/manage/refunds/{refundRequest}/approve', [RefundController::class, 'approve']);
        Route::post('/manage/refunds/{refundRequest}/reject', [RefundController::class, 'reject']);

        Route::get('/manage/dashboard', [DashboardController::class, 'index']);
        Route::get('/manage/settlement-report', [PromoterController::class, 'settlementReport']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user()->load('roles');
})->middleware('auth:sanctum');
