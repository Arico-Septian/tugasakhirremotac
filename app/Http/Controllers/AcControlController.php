<?php

namespace App\Http\Controllers;
use App\Models\AcStatus;
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
}
