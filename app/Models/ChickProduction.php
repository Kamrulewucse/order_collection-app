<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChickProduction extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function chickProductionItems()
    {
        return $this->hasMany(ChickProductionItem::class);
    }

    public function hatchery()
    {
        return $this->belongsTo(Client::class,'hatchery_id','id');
    }
    public function hatcheryManager()
    {
        return $this->belongsTo(Client::class,'hatchery_manager_id','id');
    }
    public function locationAddressInfo()
    {
        return $this->hasOne(LocationAddressInfo::class, 'sale_order_id', 'id');
    }
}
