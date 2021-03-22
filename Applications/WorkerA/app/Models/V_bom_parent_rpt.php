<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class V_bom_parent_rpt extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'v_bom_parent_rpt'; //bom母件资料
    protected $guarded = [];
    public $timestamps = false;
}
