<?php

namespace App\Factories;
use App\Models\Rate as RateModel;

/**
 * Will create and store a new rate model in database
 */
class RateModelFactory
{
    public function create(string $from, $to, float $rate): RateModel
    {
        $rateModel = new RateModel;
        $rateModel->currency_from = $from;
        $rateModel->currency_to = $to;
        $rateModel->rate = $rate;
        $rateModel->save();

        return $rateModel;
    }
}