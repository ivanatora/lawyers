<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DB;
use App\Http\Requests;
use App\Appointment;
use App\User;

class AppointmentsController extends Controller
{
    public $aAllowedFields = ['lawyer_user_id', 'status', 'description', 'schedule_date', 'schedule_time'];

    public function index(Request $request)
    {
        $oUser = User::whereId(Auth::user()->id)->first();

        $query = Appointment::select('appointments.*');
        $query->join('users as l', 'l.id', '=', 'lawyer_user_id');
        $query->join('users as c', 'c.id', '=', 'customer_user_id');

        $aOrder   = [
            'field' => 'id',
            'direction' => 'asc'
        ];
        $aSorters = $request->input('sort', '');
        if (!empty($aSorters)) {
            $aSorters = json_decode($aSorters, true);
            if (count($aSorters) > 0) {
                if ($aSorters[0]['property'] == 'lawyer_names') {
                    $query->orderBy('l.first_name', $aSorters[0]['direction']);
                    $query->orderBy('l.last_name', $aSorters[0]['direction']);
                } else if ($aSorters[0]['property'] == 'customer_names') {
                    $query->orderBy('c.first_name', $aSorters[0]['direction']);
                    $query->orderBy('c.last_name', $aSorters[0]['direction']);
                } else {
                    $aOrder['field']     = $aSorters[0]['property'];
                    $aOrder['direction'] = $aSorters[0]['direction'];
                    $query->orderBy($aOrder['field'], $aOrder['direction']);
                }
            }
        }

        $aWhere = [];
        // show only his own appointments
        if ($oUser->type == 'customer') {
            $aWhere[] = ['customer_user_id', '=', $oUser->id];
        }
        if ($oUser->type == 'lawyer') {
            $aWhere[] = ['lawyer_user_id', '=', $oUser->id];
        }

        // grid search
        $sQueryLawyer = $request->input('query_lawyer');
        if ($sQueryLawyer) {
            $query->where(function($q) use ($sQueryLawyer) {
                $q->where('l.first_name', 'LIKE', '%'.$sQueryLawyer.'%');
                $q->orWhere('l.last_name', 'LIKE', '%'.$sQueryLawyer.'%');
            });
        }

        $sQueryLawyer = $request->input('query_customer');
        if ($sQueryLawyer) {
            $query->where(function($q) use ($sQueryLawyer) {
                $q->where('c.first_name', 'LIKE', '%'.$sQueryLawyer.'%');
                $q->orWhere('c.last_name', 'LIKE', '%'.$sQueryLawyer.'%');
            });
        }

        $query->where($aWhere);

        // pagination
        $iTotal = $query->count();

        $iOffset = $request->input('start', 0);
        $iLimit  = $request->input('limit', 5);
        $iPage   = $request->input('page', -1);
        if ($iPage > -1) {
            $query->limit($iLimit)->offset($iLimit * ($iPage - 1));
        } else {
            $query->limit($iLimit)->offset($iOffset);
        }

//        DB::enableQueryLog();
        $tmp = $query->with('lawyer', 'customer')->get();
//        Log::info(DB::getQueryLog());

        // add some virtual fields
        foreach ($tmp as $idx => $item) {
            $tmp[$idx]->customer_names = $item->customer->first_name.' '.$item->customer->last_name;
            $tmp[$idx]->lawyer_names   = $item->lawyer->first_name.' '.$item->lawyer->last_name;

            // if user is lawyer, check for appointment conflicts
            $bIsConflicting = 0;
            if ($oUser->type == 'lawyer' && $item->status != 'rejected') {
                $bIsConflicting = Appointment::where([
                        ['id', '!=', $item->id],
                        ['lawyer_user_id', '=', $oUser->id],
                        ['schedule_date', '=', $item->schedule_date],
                        ['schedule_time', '=', $item->schedule_time],
                    ])->whereIn('status', ['new', 'approved'])->count();
            }
            $tmp[$idx]->is_conflicting = $bIsConflicting;
        }

        return response()->json(['success' => true, 'total' => $iTotal, 'data' => $tmp]);
    }

    public function show(Request $request, $id)
    {
        $oRecord = Appointment::find($id);
        if (!$oRecord) {
            return response()->json(['success' => false, 'error' => 'Record not found']);
        }

        return response()->json(['success' => true, 'data' => $oRecord[0]]);
    }

    public function store(Request $request)
    {
        $oUser = User::whereId(Auth::user()->id)->first();

        $oRecord                   = new Appointment();
        $oRecord->status           = $request->input('appointment.status', 'new');
        $oRecord->lawyer_user_id   = $request->input('appointment.lawyer_user_id', '');
        $oRecord->description      = $request->input('appointment.description', '');
        $oRecord->schedule_date    = $request->input('appointment.schedule_date', '');
        $oRecord->schedule_time    = $request->input('appointment.schedule_time', '');
        $oRecord->customer_user_id = $oUser->id;


        if (empty($oRecord->schedule_date)) {
            return response()->json(['success' => false, 'error' => 'Missing `schedule_date` in request']);
        }

        if (empty($oRecord->schedule_time)) {
            return response()->json(['success' => false, 'error' => 'Missing `schedule_time` in request']);
        }

        if (empty($oRecord->lawyer_user_id)) {
            return response()->json(['success' => false, 'error' => 'Missing `lawyer_user_id` in request']);
        }

        try {
            $oRecord->save();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'error' => 'Database error']);
        }

        return response()->json(['success' => true, 'data' => $oRecord]);
    }

    public function update(Request $request, $id)
    {
        $oUser = User::whereId(Auth::user()->id)->first();

        $aWhere = [
            ['id', '=', $id]
        ];
        // allow access to only his own records
        if ($oUser->type == 'customer') {
            $aWhere[] = ['customer_user_id', '=', $oUser->id];
        }
        if ($oUser->type == 'lawyer') {
            $aWhere[] = ['lawyer_user_id', '=', $oUser->id];
        }

        $oRecord = Appointment::where($aWhere)->first();
        if (!$oRecord) {
            return response()->json(['success' => false, 'error' => 'Record not found']);
        }

        $aUpdateData = $request->input('appointment');

        if ($oUser->type == 'lawyer') {
            $this->aAllowedFields = ['status', 'description', 'schedule_date', 'schedule_time'];
        }

        foreach ($aUpdateData as $sKey => $sValue) {
            if (!in_array($sKey, $this->aAllowedFields)) continue;
            $oRecord->$sKey = $sValue;
        }

        try {
            $oRecord->save();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'error' => 'Database error']);
        }

        // refresh some fields
        $oRecord = Appointment::find($id);

        return response()->json(['success' => true, 'data' => $oRecord]);
    }

    public function destroy(Request $request, $id)
    {
        $oUser = User::whereId(Auth::user()->id)->first();

        $aWhere = [
            ['id', '=', $id]
        ];
        // allow access to only his own records
        if ($oUser->type == 'customer') {
            $aWhere[] = ['customer_user_id', '=', $oUser->id];
        }
        if ($oUser->type == 'lawyer') {
            $aWhere[] = ['lawyer_user_id', '=', $oUser->id];
        }

        $oRecord = Appointment::where($aWhere)->first();
        if (!$oRecord) {
            return response()->json(['success' => false, 'error' => 'Record not found']);
        }

        $oRecord->delete();
        return response()->json(['success' => true, 'data' => $oRecord]);
    }
}