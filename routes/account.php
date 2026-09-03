<?php
use Illuminate\Support\Facades\Route;
Route::livewire("accounts","pages::accounting.accounts")->name("accounts.index");
Route::livewire("accounts/create","pages::accounting.create")->name("accounts.create");
Route::livewire("accounts/{id}/edit","pages::accounting.edit")->name("accounts.edit");