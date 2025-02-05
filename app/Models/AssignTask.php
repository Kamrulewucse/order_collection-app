<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignTask extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function srOrDoctor()
    {
        return $this->belongsTo(Client::class,'sr_doctor_id','id');
    }
}
