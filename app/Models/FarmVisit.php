<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmVisit extends Model
{
    use HasFactory,SoftDeletes;

    public function doctor()
    {
        return $this->belongsTo(Client::class, 'doctor_id');
    }
    public function farm()
    {
        return $this->belongsTo(Client::class, 'farm_id');
    }
}
