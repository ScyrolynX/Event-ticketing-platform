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

Route::get('/my-tickets', function () {
    return view('orders.index');
});

Route::get('/login', function () {
    return view('auth.login');
});
