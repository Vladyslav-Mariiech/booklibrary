<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

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

    /**
     * Convert a validation exception into a JSON response when expected.
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'success' => false,
            'message' => 'Ошибка валидации',
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Автоматически возвращаем JSON для AJAX запросов при ошибке валидации
        if ($exception instanceof ValidationException && $request->expectsJson()) {
            return $this->invalidJson($request, $exception);
        }

        return parent::render($request, $exception);
    }
}
