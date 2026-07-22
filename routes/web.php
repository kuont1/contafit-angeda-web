<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('welcome');
})->name('dashboard');

Route::get('/', function () {
    return redirect()->route('dashboard');
});
