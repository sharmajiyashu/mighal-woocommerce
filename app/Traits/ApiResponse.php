<?php

namespace App\Traits;

trait ApiResponse
{
    function sendSuccess($message, $result = null)
    {
        $response = [
            'ResponseCode'  => 200,
            'Status'    => true,
            'Message' => $message,
            'Data' => $result
        ];
        return response()->json($response, 200);
    }

    function sendFailed($errorMessage = [], $code = 200)
    {
        $response = [
            'ResponseCode'  => $code,
            'Status'    => false,
        ];

        if (!empty($errorMessage)) {
            $response['Message'] = $errorMessage;
        }
        return response()->json($response, $code);
    }
    
}
