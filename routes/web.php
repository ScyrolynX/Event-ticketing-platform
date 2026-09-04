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

Route::get('/staff', function () {
    return view('staff.dashboard');
});

Route::get('/react/{any?}', function () {
    return view('react-app');
})->where('any', '.*');
