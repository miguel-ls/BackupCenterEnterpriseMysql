<?php

namespace BackupCenter\Core;

class ApiController
{
    public static function boot(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        $allowedOrigins = [
            'http://localhost:5173',
            'https://backup.codesicorp.net'
        ];

        if (in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Content-Type: application/json; charset=utf-8");

        if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
            http_response_code(200);
            exit;
        }
    }

    public static function method(string|array $methods): void
    {
        $methods = (array)$methods;

        if (!in_array($_SERVER["REQUEST_METHOD"], $methods)) {

            ApiResponse::error(
                "Método no permitido.",
                405
            );

        }
    }

    public static function body(): array
    {
        $body = json_decode(
            file_get_contents("php://input"),
            true
        );

        return is_array($body) ? $body : [];
    }

    public static function bearerToken(): ?string
    {
        $header = $_SERVER["HTTP_AUTHORIZATION"] ?? "";

        if (
            preg_match(
                "/Bearer\s+(.*)$/i",
                $header,
                $match
            )
        ) {
            return trim($match[1]);
        }

        return null;
    }
}