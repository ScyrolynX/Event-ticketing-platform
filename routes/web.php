<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/my-tickets', function () {
    return view('orders.index');
});

Route::get('/check-in', function () {
    return view('checkin.index');
});

Route::get('/manage/events', function () {
    return view('staff.events');
});

Route::get('/manage/dashboard', function () {
    return view('staff.dashboard-report');
});

Route::get('/manage/discounts', function () {
    return view('staff.manage-discounts');
});

Route::get('/manage/refunds', function () {
    return view('staff.manage-refunds');
});

Route::get('/manage/staff', function () {
    return view('staff.manage-staff');
});

Route::get('/staff', function () {
    return view('staff.dashboard');
});

Route::get('/react/{any?}', function () {
    return view('react-app');
})->where('any', '.*');
