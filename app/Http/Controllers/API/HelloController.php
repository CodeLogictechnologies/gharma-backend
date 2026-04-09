<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class HelloController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Hello'
        ]);
    }
}