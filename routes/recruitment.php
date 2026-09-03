<?php
use Illuminate\Support\Facades\Route;
 Route::name('jobs.')->group(function () {

      Route::prefix('job-applicants')->group(function () {
        Route::livewire('/', 'pages::applicants.all')->name('applicants.index');
        Route::livewire('/{id}', 'pages::applicants.show')->name('applicants.show');
      });

     Route::prefix('job-interview')->group(function () {
        Route::livewire('/', 'pages::interviews.all')->name('interviews.index');
        Route::livewire('/{id}/edit', 'pages::interviews.edit')->name('interviews.edit');
        Route::livewire('/{id}', 'pages::interviews.show')->name('interviews.show');
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