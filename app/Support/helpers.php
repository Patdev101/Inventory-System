<?php

use Carbon\Carbon;

if (!function_exists('format_qty')) {
    /**
     * Format a quantity/decimal value with up to 2 decimal places,
     * trimming trailing zeros (e.g. 12.0000 -> "12", 5.5000 -> "5.5").
     */
    function format_qty(mixed $value): string
    {
        $formatted = number_format((float) $value, 2, '.', '');

        if (str_contains($formatted, '.')) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, '.');
        }

        return $formatted;
    }
}

if (!function_exists('format_date')) {
    /**
     * Format a date as Month/Day/Year (e.g. 09/08/2026).
     */
    function format_date(mixed $date): ?string
    {
        if (!$date) {
            return null;
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format('m/d/Y');
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format a date and time as Month/Day/Year h:i AM/PM
     * (e.g. 09/08/2026 02:05 PM).
     */
    function format_datetime(mixed $date): ?string
    {
        if (!$date) {
            return null;
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format('m/d/Y h:i A');
    }
}
