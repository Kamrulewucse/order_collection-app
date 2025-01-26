<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function paymentAccountHead()
    {
        return $this->belongsTo(AccountHead::class,'payment_account_head_id');
    }
    public function payeeDepositorHead()
    {
        return $this->belongsTo(AccountHead::class,'account_head_payee_depositor_id');
    }
    public function accountHead()
    {
        return $this->belongsTo(AccountHead::class);
    }
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
