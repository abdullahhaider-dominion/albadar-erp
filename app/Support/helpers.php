<?php

if (! function_exists('pkr')) {
    function pkr(float|int|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);

        return 'PKR '.number_format($value, 0, '.', ',');
    }
}

if (! function_exists('pkr_decimal')) {
    function pkr_decimal(float|int|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);

        return 'PKR '.number_format($value, 2, '.', ',');
    }
}

if (! function_exists('spreadsheet_cell')) {
    function spreadsheet_cell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
