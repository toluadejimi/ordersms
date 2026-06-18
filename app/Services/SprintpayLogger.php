<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SprintpayLogger
{
    public static function info(string $event, string $message, array $context = []): void
    {
        self::write('info', $event, $message, $context);
    }

    public static function warning(string $event, string $message, array $context = []): void
    {
        self::write('warning', $event, $message, $context);
    }

    public static function error(string $event, string $message, array $context = []): void
    {
        self::write('error', $event, $message, $context);
    }

    private static function write(string $level, string $event, string $message, array $context): void
    {
        $line = "[{$event}] {$message}";

        try {
            Log::channel('sprintpay')->{$level}($line, $context);
        } catch (\Throwable) {
            Log::{$level}("[SprintPay] {$line}", $context);
        }

        Log::{$level}("[SprintPay] {$line}", $context);
    }
}
