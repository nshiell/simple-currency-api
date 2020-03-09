<?php

namespace App\Rate;
use App\Models\Rate as RateModel;
use App\Factories\RateModelFactory;
use RuntimeException;

class RateDataFinder
{
    /** @var RateModel */
    private $rateModel;

    /** @var Updater */
    private $updater;

    /** @var RateModelFactory */
    private $rateModelFactory;

    public function __construct(RateModel $rateModel,
                                Updater $updater,
                                RateModelFactory $rateModelFactory)
    {
        $this->rateModel = $rateModel;
        $this->updater = $updater;
        $this->rateModelFactory = $rateModelFactory;
    }

    /**
     * @param string $from
     * @param string $to
     * @return array [RATE, IS_USING_CACHE]
     */
    public function getRateForFromAndTo(string $from, string $to): array
    {
        if ($from == $to) {
            return [1, false];
        }

        $rateModelNormal = $this->rateModel::fromTo($from, $to)->first();
        if ($rateModelNormal && $rateModelNormal->getIsAlive()) {
            return [$rateModelNormal->rate, true];
        }

        $rateModelReverse = $this->rateModel::fromTo($to, $from)->first();
        if ($rateModelReverse && $rateModelReverse->getIsAlive()) {
            return [1 / $rateModelReverse->rate, true];
        }

        $data = $this->updater->getAllData();
        $rate = $this->calculateRateBetweenCurrencies($data, $from, $to);
        $this->updateOrSave($rateModelNormal, $from, $to, $rate);

        return [$rate, false];
    }

    private function updateOrSave(?RateModel $dataNormal,
                                  string $from,
                                  string $to,
                                  float $rate)
    {
        if ($dataNormal) {
            $dataNormal->rate = $rate;
            $dataNormal->save();
        } else {
            $this->rateModelFactory->create($from, $to, $rate);
        }
    }

    private function calculateRateBetweenCurrencies($data, $from, $to): float
    {
        $fromRate = $this->getRateForCurrency($data, $from);
        $toRate = $this->getRateForCurrency($data, $to);

        return $toRate / $fromRate;
    }

    private function getRateForCurrency($data, string $currency): float
    {
        if ($currency == $data['base']) {
            return 1;
        }

        if (isset ($data['rates'][$currency])) {
            return $data['rates'][$currency];
        }

        throw new RuntimeException('Currency not found from API backend');
    }
}