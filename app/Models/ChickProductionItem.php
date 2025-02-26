<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChickProductionItem extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];
    public function rawProduct()
    {
        return $this->belongsTo(Product::class,'raw_product_id','id');
    }
    public function finishedProduct()
    {
        return $this->belongsTo(Product::class,'finished_product_id','id');
    }
    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }
}
