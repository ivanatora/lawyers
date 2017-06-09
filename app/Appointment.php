<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';

    public function customer()
    {
        return $this->belongsTo('App\User', 'customer_user_id');
    }

    public function lawyer()
    {
        return $this->belongsTo('App\User', 'lawyer_user_id');
    }
}