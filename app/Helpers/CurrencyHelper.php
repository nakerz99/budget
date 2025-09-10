<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Get currency symbol based on currency code
     *
     * @param string $currency
     * @return string
     */
    public static function getSymbol($currency = null)
    {
        if (!$currency && auth()->check()) {
            $currency = auth()->user()->currency;
        } elseif (!$currency) {
            $currency = 'PHP'; // Default to PHP if not authenticated
        }
        
        $symbols = [
            'PHP' => '₱',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
        ];
        
        return $symbols[$currency] ?? '₱';
    }
    
    /**
     * Format amount with currency
     *
     * @param float $amount
     * @param string $currency
     * @return string
     */
    public static function format($amount, $currency = null)
    {
        $symbol = self::getSymbol($currency);
        return $symbol . number_format(abs($amount), 2);
    }
}
