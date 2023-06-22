<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    function main ()
    {

        $response = Http::get('http://example.com');
        $result = $this->sTeam();
    }

    function test(){

        return response()->json([
            'message' => "success",
            'data' => "eiei"
        ]);
    }
}

