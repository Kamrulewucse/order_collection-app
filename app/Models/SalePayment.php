<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalePayment extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class,'client_id','id');
    }
    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class,'sale_order_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
