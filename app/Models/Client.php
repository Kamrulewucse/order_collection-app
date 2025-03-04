<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function companySaleOrders()
    {
        return $this->hasMany(SaleOrder::class, 'company_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'company_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Client::class, 'parent_id', 'id');
    }
    public function divisionalAdmin(){
        return $this->belongsTo(DivisionalUser::class,'divisional_user_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'parent_id');
    }
    public function division(){
        return $this->belongsTo(Division::class,'division_id');
    }
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }
    public function thana()
    {
        return $this->belongsTo(Thana::class, 'thana_id', 'id');
    }
    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class, 'supplier_id', 'id');
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'supplier_id', 'id');
    }

    public function saleOrders()
    {
        return $this->hasMany(SaleOrder::class, 'client_id', 'id')
            ->with('saleOrderItems');
    }
    
}
