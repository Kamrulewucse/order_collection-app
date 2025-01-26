<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLog extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Client::class);
    }
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
    public function distributionOrder()
    {
        return $this->belongsTo(DistributionOrder::class);
    }
}
