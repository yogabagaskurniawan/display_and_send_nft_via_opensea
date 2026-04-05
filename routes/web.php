<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/create','users::auth.login')->name('login')->middleware('guest');
Route::livewire('/create-weapon-template', 'admin::weapon-template-create')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get(
        '/logout',
        function () {
            Auth::logout();
            return redirect('/');
        }
    )->name('logout');

    Route::livewire('/wallet/history', 'users::wallet.wallet');
});