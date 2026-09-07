<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GoogleDrive;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\InterviewEntryPassController;

Auth::routes();
require __DIR__.'/applicant.php';
require __DIR__.'/customer.php';
Route::get('/home', [HomeController::class, 'index'])
    ->name('home')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/
Route::get("transfer",[ApplicantController::class,'transfer']);
Route::livewire("/", "pages::frontend.home")->name("front");
Route::livewire("/cart", "pages::frontend.cart")->name("cart");

Route::livewire('/product/{value}', 'pages::frontend.products.product-detail')
    ->name('product.detail');
Route::get("uploadImage",[GoogleDrive::class,"uploadImage"]);


Route::get(
    '/interviews/{interview}/entry-pass',
    InterviewEntryPassController::class
)->name('interviews.entry-pass');

  Route::get('offer-letter/download/{id}',[ApplicantController::class,"downloadletter"])
     ->name("jobs.offers.letters.download");
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/


Route::middleware('auth:admin,employee,deliveryboy,supplier')->group(function () {
   Route::livewire("dashboard", "pages::dashboard")->name('dashboard');
require __DIR__.'/finance.php';
require __DIR__.'/account.php';
require __DIR__.'/hr.php';
require __DIR__.'/inventory.php';
require __DIR__.'/logistics.php';
require __DIR__.'/products.php';
require __DIR__.'/recruitment.php';
require __DIR__.'/sales.php';
require __DIR__.'/support.php';
require __DIR__.'/report.php';
   /* Product Admin Route */

    /*Shipment Route*/
 
   /*Purchase Route*/

   /*category Route*/
  
   
 
 
  
   
 




   Route::livewire('/settings', 'pages::settings.index')->name('settings.index');

});
/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/
Route::livewire('customer_order/{order}', "pages::orders.detail")
    ->name('customer.orders.show');

//applicantportal.blade

 Route::get('/invoice/{order}', InvoiceController::class)
    ->name("invoice.download");

   