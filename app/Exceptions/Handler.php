<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (ValidationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        });
        $this->renderable(function (SuccessException $e) {
            return response()->json([
                'status' => true,
            ]);
        });

        $this->renderable(function (Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        });
    }
}
