<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::livewire('/create-weapon-template', 'admin::weapon-template-create');
Route::livewire('/create','users::auth.login')->name('login')->middleware('guest');