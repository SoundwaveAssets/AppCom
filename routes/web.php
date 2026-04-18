<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Auth\RegisterForm;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Profile\EditProfile;
use Illuminate\Support\Facades\Route;

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
});

// Routes publiques Livewire
Route::middleware('guest')->group(function () {
    Route::get('/register', RegisterForm::class)->name('register');
    Route::get('/login', LoginForm::class)->name('login');
});

// Routes client connecté
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile/edit', EditProfile::class)->name('profile.edit');
});

// Routes admin uniquement
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

require __DIR__.'/auth.php';