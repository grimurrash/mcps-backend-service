<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class SuccessException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => true,
        ]);
    }
}
