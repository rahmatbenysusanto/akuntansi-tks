<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($value): string
    {
        if ($value == 0 || $value === null) return '-';
        $formatted = number_format(abs($value), 0, ',', '.');
        return $value < 0 ? "($formatted)" : $formatted;
    }
}
