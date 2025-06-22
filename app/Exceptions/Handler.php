<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Check if it's a database connection error
        if ($exception instanceof QueryException) {
            $errorMessage = $exception->getMessage();

            // Check for specific database connection errors
            if (strpos($errorMessage, 'refused it') !== false ||
                strpos($errorMessage, 'Connection refused') !== false ||
                strpos($errorMessage, 'No connection could be made') !== false ||
                strpos($errorMessage, 'Access denied') !== false ||
                strpos($errorMessage, 'server has gone away') !== false ||
                strpos($errorMessage, 'Lost connection') !== false ||
                strpos($errorMessage, 'target machine actively refused') !== false) {

                // Return a "Connection to server lost" response
                return response()->view('errors.connection-lost', [], 503);
            }
        }

        return parent::render($request, $exception);
    }
}
