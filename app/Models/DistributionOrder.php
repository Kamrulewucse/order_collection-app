<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionOrder extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function distributionOrderItems()
    {
        return $this->hasMany(DistributionOrderItem::class);
    }
    public function saleOrders()
    {
        return $this->hasMany(SaleOrder::class);
    }

    public function sr()
    {
        return $this->belongsTo(Client::class,'sr_id','id');
    }
    public function company()
    {
        return $this->belongsTo(Client::class,'company_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
