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

$expenses = Account::firstOrCreate(
    ['code' => '6000'],
    [
        'name' => 'Expenses',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'is_group' => true,
    ]
);

Account::firstOrCreate(
    ['code' => '6100'],
    [
        'parent_id' => $expenses->id,
        'name' => 'Salary Expense',
        'type' => 'expense',
        'subtype' => 'salary',
        'normal_balance' => 'debit',
        'is_group' => false,
    ]
);

Account::firstOrCreate(
    ['code' => '6200'],
    [
        'parent_id' => $expenses->id,
        'name' => 'Rent Expense',
        'type' => 'expense',
        'subtype' => 'rent',
        'normal_balance' => 'debit',
        'is_group' => false,
    ]
);

Account::firstOrCreate(
    ['code' => '6300'],
    [
        'parent_id' => $expenses->id,
        'name' => 'Utilities Expense',
        'type' => 'expense',
        'subtype' => 'utilities',
        'normal_balance' => 'debit',
        'is_group' => false,
    ]
);

Account::firstOrCreate(
    ['code' => '6400'],
    [
        'parent_id' => $expenses->id,
        'name' => 'Marketing Expense',
        'type' => 'expense',
        'subtype' => 'marketing',
        'normal_balance' => 'debit',
        'is_group' => false,
    ]
);

Account::firstOrCreate(
    ['code' => '6500'],
    [
        'parent_id' => $expenses->id,
        'name' => 'Delivery Expense',
        'type' => 'expense',
        'subtype' => 'delivery',
        'normal_balance' => 'debit',
        'is_group' => false,
    ]
);

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

    }
}