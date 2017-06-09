<?php

namespace App\Http\Controllers;

use App\Http;
use App\Http\Requests;
use App\Mail\ForgottenPassword;
use App\Mail\WelcomeNewUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use DB;
use App\User;

class UsersController extends Controller
{
    public $aAllowedFields = ['first_name', 'last_name', 'email', 'password'];

    public function me()
    {
        $oUser = Auth::user();

        return response()->json(['success' => true, 'data' => $oUser]);
    }

}