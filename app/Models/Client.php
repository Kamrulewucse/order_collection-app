<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function companySaleOrders()
    {
     return $this->hasMany(SaleOrder::class,'company_id','id');
    }

    public function transactions()
    {
     return $this->hasMany(Transaction::class,'company_id','id');
    }
    public function purchaseOrders()
    {
     return $this->hasMany(PurchaseOrder::class,'supplier_id','id');
    }

    public function vouchers()
    {
     return $this->hasMany(Voucher::class,'company_id','id');
    }
    public function accountHead()
    {
     return $this->belongsTo(AccountHead::class,'supplier_id','id');
    }
    public function sr()
    {
     return $this->belongsTo(Client::class,'sr_id','id');
    }
    public function district()
    {
     return $this->belongsTo(District::class,'district_id','id');
    }
    public function thana()
    {
     return $this->belongsTo(Thana::class,'thana_id','id');
    }
    public function inventoryLogs()
    {
     return $this->hasMany(InventoryLog::class,'supplier_id','id');
    }
    public function products()
    {
     return $this->hasMany(Product::class,'supplier_id','id');
    }
    public function inventories()
    {
     return $this->hasMany(Inventory::class,'company_id','id');
    }
    public function distributionOrders()
    {
     return $this->hasMany(DistributionOrder::class,'company_id','id');
    }

    public function saleOrders()
    {
        return $this->hasMany(SaleOrder::class,'customer_id','id')
            ->with('distributionOrder');
    }
}
