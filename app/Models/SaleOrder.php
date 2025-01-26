<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrder extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
    public function distributionOrder()
    {
        return $this->belongsTo(DistributionOrder::class)
            ->with('sr');
    }
    public function saleOrderItems()
    {
        return $this->hasMany(SaleOrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Client::class,'customer_id','id')
            ->with('company');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
