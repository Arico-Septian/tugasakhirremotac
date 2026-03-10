<?php

namespace App\Http\Controllers;
use App\Models\AcStatus;
use App\Models\AcUnit;
use Illuminate\Http\Request;

class AcControlController extends Controller
{
public function powerOn($id)
    {
    $status = AcStatus::firstOrCreate(
    ['ac_unit_id'=>$id]
    );
    $status->power = 'ON';
    $status->save();
    return back();
    }
public function powerOff($id)
    {
    $status = AcStatus::firstOrCreate(
    ['ac_unit_id'=>$id]
    );
    $status->power = 'OFF';
    $status->save();
    return back();
    }
public function setTemp($id,$value)
    {
    $status = AcStatus::firstOrCreate(
    ['ac_unit_id'=>$id]
    );
    $status->set_temperature = $value;
    $status->save();
    return back();
    }
public function setMode($id,$mode)
    {
    $status = AcStatus::firstOrCreate(
    ['ac_unit_id'=>$id]
    );
    $status->mode = strtoupper($mode);
    $status->save();
    return back();
    }
public function togglePower($id)
    {

        $ac = AcUnit::findOrFail($id);

        $status = $ac->status;

        if(!$status){
            $status = new AcStatus();
            $status->ac_unit_id = $ac->id;
        }

        if($status->power == 'ON'){
            $status->power = 'OFF';
        }else{
            $status->power = 'ON';
        }

        $status->save();

        return back();
    }
}
