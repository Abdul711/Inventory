<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicantController;

 Route::name('jobs.')->group(function () {

      Route::prefix('job-applicants')->group(function () {
        Route::livewire('/', 'pages::applicants.all')->name('applicants.index');
        Route::livewire('/{id}', 'pages::applicants.show')->name('applicants.show');

      });
         Route::prefix("job-screenings")->group(function(){
             Route::livewire('/',"pages::screenings.index")->name("screenings.index");
            Route::livewire('/{id}/edit',"pages::screenings.edit")->name("screenings.edit");
              Route::livewire('/{id}',"pages::screenings.show")->name("screenings.show");
         });
     Route::prefix('job-interview')->group(function () {
        Route::livewire('/', 'pages::interviews.all')->name('interviews.index');
        Route::livewire('/{id}/edit', 'pages::interviews.edit')->name('interviews.edit');
        Route::livewire('/{id}', 'pages::interviews.show')->name('interviews.show');
     });
     Route::prefix("job-offer-negotiations")->group(function(){
        Route::livewire("/","pages::offer_negotiations.index")->name("offer-negotiations.index");
         Route::livewire("/create","pages::offer_negotiations.create")->name("offer-negotiations.create");
          Route::livewire("/{id}/edit","pages::offer_negotiations.edit")->name("offer-negotiations.edit");
               Route::livewire("/{id}","pages::offer_negotiations.show")->name("offer-negotiations.show");
     });

     Route::prefix('job-applications')->group(function () {
        Route::livewire('/', 'pages::job_application.all')->name('applications.index');
        Route::livewire('/{id}/edit', 'pages::job_application.edit')->name('applications.edit');
        Route::livewire('/{id}', 'pages::job_application.show')->name('applications.show');
     });

     Route::prefix('job-postings')->group(function () {
        Route::livewire('/', 'pages::job_postings.all')->name('index');
        Route::livewire('/create', 'pages::job_postings.create')->name('create');
        Route::livewire('/{id}/edit', 'pages::job_postings.edit')->name('edit');
        Route::livewire('/{id}', 'pages::job_postings.show')->name('show');
     });
     Route::prefix("job-offers")->group(function(){
           Route::livewire('/', 'pages::job_offer.all')->name('offers.index');
           Route::livewire('/{id}/edit','pages::job_offer.edit')->name("offers.edit");
           Route::livewire('/{id}','pages::job_offer.show')->name("offers.show");
     });
   
});