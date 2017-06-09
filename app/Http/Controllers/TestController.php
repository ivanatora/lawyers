<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\User;
use DB;

class TestController extends Controller
{

    public function test()
    {
        $oUser             = new User();
        $oUser->first_name = 'Lawyer';
        $oUser->last_name  = 'Lawyer';
        $oUser->email      = 'lawyer@test.com';
        $oUser->password   = Hash::make('lawyer');
        $oUser->type       = 'lawyer';
        $oUser->save();

        $oUser             = new User();
        $oUser->first_name = 'Customer';
        $oUser->last_name  = 'Customer';
        $oUser->email      = 'customer@test.com';
        $oUser->password   = Hash::make('customer');
        $oUser->type       = 'customer';
        $oUser->save();
    }
}