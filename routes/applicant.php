<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
Route::livewire('/career', 'pages::frontend.career.index')->name('career');
Route::livewire('/jobdetail/{id}','pages::frontend.career.jobdetail')->name('jobdetail');
Route::livewire('/applicantportal', 'pages::frontend.career.applicantportal')->name('applicantportal')->middleware("applicant");
Route::livewire('/applicantauth', 'pages::frontend.career.auth')->name('applicantauth');
Route::livewire('/applicantportalbackup', 'pages::frontend.career.backup')->name('applicantbackup')->middleware("applicant");
Route::get("jobapplicationreport",[ApplicationController::class,"report"])->name("jobs.reports")->middleware(["auth"]);