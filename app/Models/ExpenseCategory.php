<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
    public function account()
{
    return $this->belongsTo(Account::class);
}
}