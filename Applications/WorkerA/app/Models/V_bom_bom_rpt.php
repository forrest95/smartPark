<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class V_bom_bom_rpt extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'v_bom_bom_rpt'; //bom资料
    protected $guarded = [];
    public $timestamps = false;
}
