<?php

namespace App\Rate;

use GuzzleHttp\Client;

class Updater
{
    /** @var Client */
    private $guzzleClient;

    public function __construct(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getAllData()
    {
        return json_decode(
            $this->guzzleClient->request('GET')->getBody(),
            true
        );
    }
}