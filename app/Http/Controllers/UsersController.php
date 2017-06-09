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
    public $aAllowedFields = ['first_name', 'last_name', 'email', 'password', 'type'];

    public function me()
    {
        $oUser = Auth::user();

        return response()->json(['success' => true, 'data' => $oUser]);
    }

    public function register(Request $request)
    {
        $sFirstName = $request->input('user.first_name', '');
        $sLastName  = $request->input('user.last_name', '');
        $sPassword  = $request->input('user.password', '');
        $sEmail     = $request->input('user.email', '');
        $sType      = $request->input('user.type', '');

        if (!$sEmail) {
            return response()->json(['success' => false, 'error' => 'Missing `email` in request']);
        }
        if (!$sPassword) {
            return response()->json(['success' => false, 'error' => 'Missing `password` in request']);
        }

        if ($sType != 'customer' && $sType != 'lawyer') {
            return response()->json(['success' => false, 'error' => 'Wrong `type` in request. Allowed values are `customer` and `lawyer`']);
        }

        // test for unique email
        $bEmailExists = User::where('email', $sEmail)->count();
        if ($bEmailExists) {
            return response()->json(['success' => false, 'error' => 'User with that e-mail already exists']);
        }

        $oRecord             = new User;
        $oRecord->first_name = $sFirstName;
        $oRecord->last_name  = $sLastName;
        $oRecord->password   = Hash::make($sPassword);
        $oRecord->email      = $sEmail;
        $oRecord->type       = $sType;

        $oRecord->password_reset_key     = md5(time());
        $oRecord->password_reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        try {
            $oRecord->save();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'error' => 'Database error']);
        }


        return response()->json(['success' => true, 'data' => $oRecord]);
    }
}