<?php

namespace App\Support;

final class Feedback
{
    public static function message(mixed $message, string $fallback = 'Terjadi masalah. Silakan coba lagi.'): string
    {
        if (!is_string($message) || trim($message) === '') return $fallback;
        // Exceptions and infrastructure diagnostics belong in logs, never in notifications.
        if (preg_match('/SQLSTATE|Stack trace|PDOException|Exception:|vendor[\\\\\/]|<[^>]+>|\bselect\s+.+\s+from\b|(?:password|token|secret)\s*[=:]/is', $message)) return $fallback;
        return mb_strimwidth(trim($message), 0, 400, '…');
    }
}
