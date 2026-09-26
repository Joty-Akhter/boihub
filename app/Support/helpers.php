<?php

if (! function_exists('money')) {
    function money(float|string|null $amount): string
    {
        return config('shop.currency') . number_format((float) $amount, 2);
    }
}
