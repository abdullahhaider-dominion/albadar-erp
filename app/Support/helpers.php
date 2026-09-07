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
