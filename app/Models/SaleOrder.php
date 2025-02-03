<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrder extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function saleOrderItems()
    {
        return $this->hasMany(SaleOrderItem::class);
    }

    public function sr()
    {
        return $this->belongsTo(Client::class,'sr_id','id');
    }
    public function client()
    {
        return $this->belongsTo(Client::class,'client_id','id');
    }
    public function locationAddressInfo()
    {
        return $this->hasOne(LocationAddressInfo::class, 'sale_order_id', 'id');
    }
}
