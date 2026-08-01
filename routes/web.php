<?php

use Illuminate\Support\Facades\Route;

Route::get('/sw.js', function () {
    return response('', 200)->header('Content-Type', 'application/javascript');
});

Route::get('/dashboard', function () {
    return view('welcome');
})->name('dashboard');

Route::get('/', function () {
    return redirect()->route('dashboard');
});
