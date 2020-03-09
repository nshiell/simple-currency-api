<?php

namespace App\Factories;

use App\Rate\RateDataFinder;
use App\Rate\Rate;

/**
 * Used to create a rate that can convert values directly
 */
class RateFactory
{
    /** @var RateDataFinder */
    private $rateDataFinder;

    /** @var array */
    private $allowedCurrencies;

    public function __construct(RateDataFinder $rateDataFinder,
                                array          $allowedCurrencies)
    {
        $this->rateDataFinder = $rateDataFinder;
        $this->allowedCurrencies = $allowedCurrencies;
    }

    /**
     * Will try and use the cache
     * Will use reverse rates
     * Will perform an API query if needed and cache the result
     */
    public function createRate(string $from, string $to): Rate
    {
        $this->throwCurrencyCodeValidationException($from);
        $this->throwCurrencyCodeValidationException($to);

        return new Rate(
            ...$this->rateDataFinder->getRateForFromAndTo($from, $to)
        );
    }

    /**
     * Validation
     */
    private function throwCurrencyCodeValidationException(string $currencyCode)
    {
        if (!in_array($currencyCode, $this->allowedCurrencies)) {
            throw new \UnexpectedValueException(
                'currency code ' . $currencyCode . ' not supported'
            );
        }
    }
}