<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class V_bom_opcomponent_rpt extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'v_bom_opcomponent_rpt'; //bom 子件资料
    protected $guarded = [];
    public $timestamps = false;
}
