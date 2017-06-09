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

        $aOrder     = [
            'field' => 'id',
            'direction' => 'asc'
        ];
        $sSortField = $request->input('sort_field');
        if ($sSortField && in_array($sSortField, $this->aAllowedFields)) {
            $aOrder['field'] = $sSortField;
        }
        $sSortDir = $request->input('sort_dir');
        if ($sSortDir && in_array($sSortDir, ['asc', 'desc'])) {
            $aOrder['direction'] = $sSortDir;
        }
        $query = Appointment::orderBy($aOrder['field'], $aOrder['direction']);

        $aWhere     = [];
        $aQueryData = $request->all();
        foreach ($aQueryData as $sKey => $sValue) {
            if (preg_match('/^query_(.+?)$/', $sKey, $aMatches)) {
                $sField = $aMatches[1];
                if (!in_array($sField, $this->aAllowedFields)) continue;
                if (preg_match('/_id$/', $sField)) {
                    $aWhere[] = [$sField, '=', $sValue];
                } else {
                    $aWhere[] = [$sField, 'LIKE', '%'.$sValue.'%'];
                }
            }
        }

        if ($oUser->type == 'customer') {
            $aWhere[] = ['customer_user_id', '=', $oUser->id];
        }
        if ($oUser->type == 'lawyer') {
            $aWhere[] = ['lawyer_user_id', '=', $oUser->id];
        }

        $sQueryGeneral = $request->input('query_general');
        if ($sQueryGeneral) {
            $query->where(function($q) use ($sQueryGeneral) {
                $q->where('title', 'LIKE', '%'.$sQueryGeneral.'%');
//                $q->orWhere('last_name', 'LIKE', '%'.$sQueryGeneral.'%');
            });
        }
//        Log::info(['w' => $aWhere, 'qd' => $aQueryData]);

        $query->where($aWhere);

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
        $tmp = $query->with('customer', 'lawyer')->get();
//        Log::info(DB::getQueryLog());
        // add some virtual fields
        foreach ($tmp as $idx => $item) {
            $tmp[$idx]->customer_names = $item->customer->first_name.' '.$item->customer->last_name;
            $tmp[$idx]->lawyer_names   = $item->lawyer->first_name.' '.$item->lawyer->last_name;
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
            'id', '=', $id
        ];
        // allow access to only his own records
        if ($oUser->type == 'customer') {
            $aWhere[] = ['customer_user_id', '=', $oUser->id];
        }
        if ($oUser->type == 'lawyer') {
            $aWhere[] = ['lawyer_user_id', '=', $oUser->id];
        }

        $oRecord = Appointment::where($aWhere)->get();
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
            'id', '=', $id
        ];
        // allow access to only his own records
        if ($oUser->type == 'customer') {
            $aWhere[] = ['customer_user_id', '=', $oUser->id];
        }
        if ($oUser->type == 'lawyer') {
            $aWhere[] = ['lawyer_user_id', '=', $oUser->id];
        }

        $oRecord = Appointment::where($aWhere)->get();
        if (!$oRecord) {
            return response()->json(['success' => false, 'error' => 'Record not found']);
        }

        $oRecord->delete();
        return response()->json(['success' => true, 'data' => $oRecord]);
    }
}