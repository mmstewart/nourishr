<?php

use App\Http\Controllers\Auth\GoogleAuthCallbackController;
use App\Http\Controllers\Auth\GoogleAuthRedirectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('auth/google/callback', GoogleAuthCallbackController::class)->name('google.callback');
Route::get('auth/google/redirect', GoogleAuthRedirectController::class)->name('google.redirect');

Route::get('login', [LoginController::class, 'create'])->name('login');
Route::post('login', [LoginController::class, 'store'])->name('login.store');

Route::get('register', [RegisterController::class, 'create'])->name('register');
Route::post('register', [RegisterController::class, 'store'])->name('register.store');
