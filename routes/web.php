<?php

$router->get('/', function () use ($router) {
    return view('page');
});

/** @todo make this a DELETE request not a GET */
// this call will clear the exchange rates cache
$router->get('/api/cache/clear', function () {
    app(\App\Rate\RateCacheClear::class)->deleteAll();

    return response()->json([
        'error' => 0,
        'msg'   => 'ok'
    ]);
});

// this call will return a basic text string
$router->get('/api/exchange/info', function () {
    return response()->json([
        'error' => 0,
        'msg'   => env('ABOUT_INFO')
    ]);
});

// this call will convert 100 usd into euros, and return the amount
$router->get('/api/exchange/{value}/{from}/{to}', function ($from, $to, $value) {
    try {
        $rate = app(\App\Factories\RateFactory::class)->createRate($from, $to);
    } catch (\UnexpectedValueException $e) {
        return response()->json([
            'error' => 1,
            'msg'   => $e->getMessage()
        ])->setStatusCode(404);
    }

    return response()->json([
        'error'     => 0,
        'amount'    => $rate->getValueExchanged($value),
        'fromCache' => (int) $rate->getIsFromCache()
    ]);
});

// Any invalid urls submitted to the API must return
$router->get('/{all:.*}', function () {
    return response()->json([
        'error' => 1,
        'msg'   => 'invalid request'
    ])->setStatusCode(404);
});