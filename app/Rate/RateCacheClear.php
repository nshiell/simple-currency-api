<?php

namespace App\Rate;
use App\Models\Rate as RateModel;

class RateCacheClear
{
    /** @var RateModel */
    private $rateModel;

    public function __construct(RateModel $rateModel)
    {
        $this->rateModel = $rateModel;
    }

    public function deleteAll()
    {
        $this->rateModel->truncate();
    }
}