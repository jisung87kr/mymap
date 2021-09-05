<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\ToiletController;

class ToiletApiController extends ToiletController
{
    public function responseIndex($data)
    {
        return response()->json(
            [
                'success' => true,
                'response' => $data['result'],
            ],
            201,
            [],
            JSON_PRETTY_PRINT);
    }
}