<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function error(string $message, int $status, ?array $errors = null): JsonResponse
    {
        $payload = [
            'message' => $message,
            'status' => $status,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
