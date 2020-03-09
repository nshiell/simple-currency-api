<?php

namespace App\Rate;

class Rate
{
    /** @var float */
    protected $multiplier;

    /** @var bool */
    protected $isFromCache;

    public function __construct(float $multiplier, bool $isFromCache)
    {
        $this->multiplier = $multiplier;
        $this->isFromCache = $isFromCache;
    }

    /**
     * Given a value, will convert it to 2 decimal places
     */
    public function getValueExchanged(float $value): float
    {
        return round($value * $this->multiplier, 2);
    }

    /** Was the rate cached? */
    public function getIsFromCache(): bool
    {
        return $this->isFromCache;
    }
}