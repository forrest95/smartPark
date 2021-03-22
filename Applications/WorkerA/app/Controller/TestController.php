<?php

namespace App\Controller;
use App\Controller\Controller;
use WorkerF\Http\Requests;
use App\Models\Test;

class TestController extends Controller
{
    public function test(Test $test, Requests $request)
    {
    		$aa=["aa"=>"bb",'cc'=>333];
    		return $aa;
    		return json_encode($aa);

        $rst = $test->getData();

        return $rst;
    }
}
