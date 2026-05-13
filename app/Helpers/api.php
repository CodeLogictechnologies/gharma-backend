<?php

if (!function_exists('apiResponse')) {
    function apiResponse($type = '', $message = '', $data = null, $code = 200)
    {
        return response()->json([
            'ty$type'  => $type,
            'message' => $message,
            'data'    => $data
        ], $code);
    }
}

if (!function_exists('apiResponseQuery')) {
    function apiResponseQuery($type = '', $message = 'Something went wrong', $data = null, $code = 200)
    {
        return response()->json([
            'type'    => $type,
            'message' => $message,
            'data'    => $data
        ], $code);
    }
}
