<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{

    public function sendSuccessResponse($message, $data)
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data'    => $data,
        ];
        return response()->json($response, 200);
    }
    public function sendErrorResponse($message, $errorMessages = [], $code = 404)
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
}
