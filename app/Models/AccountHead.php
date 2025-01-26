<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountHead extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class);
    }
    public function openingTransactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class)->with('payeeDepositorHead');
    }
}
