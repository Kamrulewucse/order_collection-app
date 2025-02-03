<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function sr()
    {
        return $this->belongsTo(Client::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Client::class);
    }
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
