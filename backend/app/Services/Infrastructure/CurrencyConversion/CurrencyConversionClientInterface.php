<?php

namespace Evently\Services\Infrastructure\CurrencyConversion;

use Brick\Money\Currency;
use Evently\Values\MoneyValue;

interface CurrencyConversionClientInterface
{
    public function convert(Currency $fromCurrency, Currency $toCurrency, float $amount): MoneyValue;
}
