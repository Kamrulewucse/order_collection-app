<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Client::class);
    }
    public function vouchers()
    {
        return $this->hasMany(Voucher::class,'purchase_order_id');
    }
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
