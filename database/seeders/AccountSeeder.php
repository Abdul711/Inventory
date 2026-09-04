<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
             $assets = Account::firstOrCreate(
            ['code' => '1000'],
            [
                'name' => 'Assets',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'is_group' => true,
            ]
        );

        $currentAssets = Account::firstOrCreate(
            ['code' => '1100'],
            [
                'parent_id' => $assets->id,
                'name' => 'Current Assets',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'is_group' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => '1110'],
            [
                'parent_id' => $currentAssets->id,
                'name' => 'Cash in Hand',
                'type' => 'asset',
                'subtype' => 'cash',
                'normal_balance' => 'debit',
            ]
        );

        Account::firstOrCreate(
            ['code' => '1120'],
            [
                'parent_id' => $currentAssets->id,
                'name' => 'Bank',
                'type' => 'asset',
                'subtype' => 'bank',
                'normal_balance' => 'debit',
            ]
        );

        Account::firstOrCreate(
            ['code' => '1130'],
            [
                'parent_id' => $currentAssets->id,
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'subtype' => 'receivable',
                'normal_balance' => 'debit',
            ]
        );

        Account::firstOrCreate(
            ['code' => '1140'],
            [
                'parent_id' => $currentAssets->id,
                'name' => 'Inventory',
                'type' => 'asset',
                'subtype' => 'inventory',
                'normal_balance' => 'debit',
            ]
        );


          Account::updateOrCreate(
                ['code' => '1150'],
                [
                    'parent_id' => $currentAssets->id,
                    'name' => 'Employee Salary Advances',
                    'type' => 'asset',
                    'subtype' => 'employee_advance',
                    'is_postable' => true,
                    'normal_balance' => 'debit',
                    'is_group' => false,
                    'is_active' => true,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | LIABILITIES
        |--------------------------------------------------------------------------
        */

        $liabilities = Account::firstOrCreate(
            ['code' => '2000'],
            [
                'name' => 'Liabilities',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'is_group' => true,
            ]
        );

        $currentLiabilities = Account::firstOrCreate(
            ['code' => '2100'],
            [
                'parent_id' => $liabilities->id,
                'name' => 'Current Liabilities',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'is_group' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => '2110'],
            [
                'parent_id' => $currentLiabilities->id,
                'name' => 'Accounts Payable',
                'type' => 'liability',
                'subtype' => 'payable',
                'normal_balance' => 'credit',
            ]
        );
       
         
$revenue = Account::firstOrCreate(
    ['code' => '4000'],
    [
        'name' => 'Revenue',
        'type' => 'revenue',
        'normal_balance' => 'credit',
        'is_group' => true,
    ]
);

Account::firstOrCreate(
    ['code' => '4100'],
    [
        'parent_id' => $revenue->id,
        'name' => 'Sales Revenue',
        'type' => 'revenue',
        'subtype' => 'sales',
        'normal_balance' => 'credit',
        'is_group' => false,
    ]
);


/*
|--------------------------------------------------------------------------
| EXPENSES
|--------------------------------------------------------------------------
*/



$equity = Account::firstOrCreate(
    ['code' => '3000'],
    [
        'name' => 'Equity',
        'type' => 'equity',
        'normal_balance' => 'credit',
        'is_group' => true,
    ]
);

Account::firstOrCreate(
    ['code' => '3100'],
    [
        'parent_id' => $equity->id,
        'name' => 'Owner Capital',
        'type' => 'equity',
        'subtype' => 'capital',
        'normal_balance' => 'credit',
        'is_group' => false,
    ]
);

$expenses = Account::updateOrCreate(
            ['code' => '5000'],
            [
                'name' => 'Expenses',
                'type' => 'expense',
                'subtype' => 'operating_expense',
                'parent_id' => null,
                'is_postable' => false,
                'normal_balance' => 'debit',
                'is_group' => true,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 5100 - Payroll Expenses
        |--------------------------------------------------------------------------
        */

        $payrollExpenses = Account::updateOrCreate(
            ['code' => '5100'],
            [
                'name' => 'Payroll Expenses',
                'type' => 'expense',
                'subtype' => 'payroll',
                'parent_id' => $expenses->id,
                'is_postable' => false,
                'normal_balance' => 'debit',
                'is_group' => true,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Payroll child accounts
        |--------------------------------------------------------------------------
        */


                $liabilities = [
                [
                    'code' => '2120',
                    'name' => 'Salary Payable',
                ],
                [
                    'code' => '2130',
                    'name' => 'Payroll Tax Payable',
                ],
                [
                    'code' => '2140',
                    'name' => 'Provident Fund Payable',
                ],
                [
                    'code' => '2150',
                    'name' => 'Other Payroll Deductions Payable',
                ],
                [
                    'code' => '2160',
                    'name' => 'Employer Contribution Payable',
                ],
            ];

            foreach ($liabilities as $liability) {
                Account::updateOrCreate(
                    ['code' => $liability['code']],
                    [
                        'parent_id' => $currentLiabilities->id,
                        'name' => $liability['name'],
                        'type' => 'liability',
                        'subtype' => 'payroll',
                        'is_postable' => true,
                        'normal_balance' => 'credit',
                        'is_group' => false,
                        'is_active' => true,
                    ]
                );
            }

        Account::updateOrCreate(
            ['code' => '5110'],
            [
                'name' => 'Basic Salary Expense',
                'type' => 'expense',
                'subtype' => 'payroll',
                'parent_id' => $payrollExpenses->id,
                'is_postable' => true,
                'normal_balance' => 'debit',
                'is_group' => false,
                'is_active' => true,
            ]
        );

        Account::updateOrCreate(
            ['code' => '5120'],
            [
                'name' => 'Allowance Expense',
                'type' => 'expense',
                'subtype' => 'payroll',
                'parent_id' => $payrollExpenses->id,
                'is_postable' => true,
                'normal_balance' => 'debit',
                'is_group' => false,
                'is_active' => true,
            ]
        );

        Account::updateOrCreate(
            ['code' => '5130'],
            [
                'name' => 'Overtime Expense',
                'type' => 'expense',
                'subtype' => 'payroll',
                'parent_id' => $payrollExpenses->id,
                'is_postable' => true,
                'normal_balance' => 'debit',
                'is_group' => false,
                'is_active' => true,
            ]
        );

        Account::updateOrCreate(
            ['code' => '5140'],
            [
                'name' => 'Bonus Expense',
                'type' => 'expense',
                'subtype' => 'payroll',
                'parent_id' => $payrollExpenses->id,
                'is_postable' => true,
                'normal_balance' => 'debit',
                'is_group' => false,
                'is_active' => true,
            ]
        );

        Account::updateOrCreate(
            ['code' => '5150'],
            [
                'name' => 'Employer Contribution',
                'type' => 'expense',
                'subtype' => 'payroll',
                'parent_id' => $payrollExpenses->id,
                'is_postable' => true,
                'normal_balance' => 'debit',
                'is_group' => false,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Other Operating Expenses
        |--------------------------------------------------------------------------
        */

        Account::updateOrCreate(
            ['code' => '5200'],
            [
                'name' => 'Rent Expense',
                'type' => 'expense',
                'subtype' => 'operating_expense',
                'parent_id' => $expenses->id,
                'is_postable' => true,
                'normal_balance' => 'debit',
                'is_group' => false,
                'is_active' => true,
            ]
        );

        Account::updateOrCreate(
            ['code' => '5300'],
            [
                'name' => 'Utilities Expense',
                'type' => 'expense',
                'subtype' => 'operating_expense',
                'parent_id' => $expenses->id,
                'is_postable' => true,
                'normal_balance' => 'debit',
                'is_group' => false,
                'is_active' => true,
            ]
        );

        Account::updateOrCreate(
            ['code' => '5400'],
            [
                'name' => 'Marketing Expense',
                'type' => 'expense',
                'subtype' => 'operating_expense',
                'parent_id' => $expenses->id,
                'is_postable' => true,
                'normal_balance' => 'debit',
                'is_group' => false,
                'is_active' => true,
            ]
        );

        Account::updateOrCreate(
            ['code' => '5500'],
            [
                'name' => 'Delivery Expense',
                'type' => 'expense',
                'subtype' => 'operating_expense',
                'parent_id' => $expenses->id,
                'is_postable' => true,
                'normal_balance' => 'debit',
                'is_group' => false,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 5600 - Other Operating Expenses
        |--------------------------------------------------------------------------
        */

        Account::updateOrCreate(
            ['code' => '5600'],
            [
                'name' => 'Other Operating Expenses',
                'type' => 'expense',
                'subtype' => 'operating_expense',
                'parent_id' => $expenses->id,
                'is_postable' => false,
                'normal_balance' => 'debit',
                'is_group' => true,
                'is_active' => true,
            ]
        );

    }
}