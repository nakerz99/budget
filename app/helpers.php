<?php

use App\Helpers\CurrencyHelper;

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol for the current user
     *
     * @param string|null $currency
     * @return string
     */
    function currency_symbol($currency = null)
    {
        return CurrencyHelper::getSymbol($currency);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format amount with currency symbol
     *
     * @param float $amount
     * @param string|null $currency
     * @return string
     */
    function format_currency($amount, $currency = null)
    {
        return CurrencyHelper::format($amount, $currency);
    }
}
