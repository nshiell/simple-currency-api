<?php

namespace App\Tests\Rate;

use App\Models\Rate as RateModel;

class RateModelFake extends RateModel
{
    static public $fromToReturnsCallback;

    public function __construct(array $attributes = []) {}

    static public function fromTo()
    {
        return self::$fromToReturnsCallback->__invoke(func_get_args());
    }
}