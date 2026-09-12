<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ReportController;

    Route::prefix("departments")->name("departments.")->group(function () {
        Route::livewire("/", "pages::departments.all")->name("index");
        Route::livewire("/create", "pages::departments.create")->name("create");
        Route::livewire("/{id}", "pages::departments.show")->name("show");
        Route::livewire('/{id}/edit','pages::departments.edit')->name('edit');
    });
    Route::prefix("designation")->name("departments.designations.")->group(function(){
        Route::livewire("/", "pages::departments.designations.all")->name("index");
        Route::livewire("/create", "pages::departments.designations.create")->name("create");
        Route::livewire("/{id}", "pages::departments.designations.show")->name("show");
        Route::livewire('/{id}/edit','pages::departments.designations.edit')->name('edit');
    });


    Route::prefix("employees")->name("employees.")->group(function () {
        Route::livewire("/", "pages::employees.all")->name("index");
        Route::livewire("/create", "pages::employees.create")->name("create");
        Route::livewire("/{id}/bank-account", "pages::employees.bankaccount")->name("bank-account");
        Route::livewire("/{id}", "pages::employees.show")->name("show");
        Route::livewire("/{id}/edit", "pages::employees.edit")->name("edit");
       
        Route::get('/{employee}/report', [ReportController::class, 'report'])->name('report');
        Route::get('/{employee}/cnic/download', [EmployeeController::class, 'downloadCnic'])->name('cnic.download');
        Route::get('/{employee}/card/download',[EmployeeController::class, 'downloadCard'])->name('card.download');
        Route::get('/{employee}/{salary_payment_id}/payslip/download', [EmployeeController::class, 'downloadPayslip'])->name('payslip.download');
    });
    Route::get("all-employee",[ReportController::class,"allemployee"])->name("employees.all");
      Route::livewire("/salary-payment", "pages::employees.salary")->name("employees.salary");

      
    Route::prefix('shifts')->name('shifts.')->group(function () {
     Route::livewire('/', 'pages::shifts.all')->name('index');
     Route::livewire('/create', 'pages::shifts.create')->name('create');
     Route::livewire('/{id}/edit', 'pages::shifts.edit')->name('edit');
    });
    
        Route::prefix("salaries")->name("salaries.")->group(function () {
         Route::livewire("/", "pages::employees.salary")->name("all");
        });
        Route::prefix('payrolls')->name('payrolls.')->group(function () {
         Route::livewire('/', 'pages::payrolls.all')->name('index');
         Route::livewire('/{id}', 'pages::payrolls.show')->name('show');
        });
    
       Route::prefix("attendance")->name("attendances.")->group(function(){
          
              Route::livewire("/","pages::attendance.all")->name("index");
       });
        Route::prefix("leave")->name("leaves.")->group(function(){
          
              Route::livewire("/","pages::leaves.all")->name("index");
       });