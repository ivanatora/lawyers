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

 
    public function create_admin()
    {
        $oUser           = new User();
        $oUser->name     = 'Admin';
        $oUser->email    = 'admin@admin.com';
        $oUser->password = Hash::make('test123');
        $oUser->save();
    }
}