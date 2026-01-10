<?php

namespace App\Services;

class CurrencyService
{
    /**
     * Tasa de cambio fija para la versión 1.0 (según PRD)
     * En el futuro esto podría leerse de una tabla o API externa.
     */
    protected float $exchangeRate = 850.00; 

    /**
     * Convierte un monto de una moneda a otra.
     * 
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency = 'ARS'): float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        if ($fromCurrency === 'USD' && $toCurrency === 'ARS') {
            return $amount * $this->exchangeRate;
        }

        if ($fromCurrency === 'ARS' && $toCurrency === 'USD') {
            return $amount / $this->exchangeRate;
        }

        return $amount;
    }

    /**
     * Obtiene la tasa de cambio actual.
     */
    public function getRate(): float
    {
        return $this->exchangeRate;
    }
}
