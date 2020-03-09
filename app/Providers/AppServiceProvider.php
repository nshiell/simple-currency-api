<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Factories\RateFactory;
use App\Rate\RateDataFinder;
use App\Models\Rate as RateModel;
use App\Rate\Updater;
use App\Factories\RateModelFactory;
use GuzzleHttp\Client;
use App\Rate\RateCacheClear;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services for affecting model changes with rates
     * Getting rates, clearing cache
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RateCacheClear::class, function () {
            return new RateCacheClear(new RateModel);
        });

        $this->app->singleton(RateFactory::class, function () {
            $client = new Client(
                ['base_uri' => env('EXCHANGE_RATE_SOURCE_URL')]
            );

            $updater = new Updater($client);

            $rateDataFinder = new RateDataFinder(
                new RateModel,
                $updater,
                new RateModelFactory,
                env('CACHE_TTL_SECONDS')
            );

            return new RateFactory(
                $rateDataFinder,
                $this->getAllowedCurrencies()
            );
        });
    }

    private function getAllowedCurrencies(): array
    {
        $arrayAsString = env('ALLOWED_CURRENCIES');
        if ($arrayAsString === null) {
            return [];
        }

        return array_unique(
            array_map('trim',
                explode(
                    ',',
                    strtoupper($arrayAsString)
                )
            )
        );
    }
}
