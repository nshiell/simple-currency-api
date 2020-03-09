<?php

namespace App\Tests\Rate;

use GuzzleHttp\Client;
use App\Rate\Updater;

class UpdaterTest extends \TestCase
{
    public function testGetAllData()
    {
        $jsonString = '{"rates":{"CAD":1.5213,"HKD":8.8089},"base":"EUR","date":"2020-03-06"}';
        $expected = [
            'rates' => [
                'CAD' => 1.5213,
                'HKD' => 8.8089
            ],
            'base' => 'EUR',
            'date' => '2020-03-06'
        ];

        $mockResponse = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getBody'])
            ->getMock();

        $mockResponse->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($jsonString));

        /** @var Client */
        $mockGuzzle = $this->getMockBuilder(Client::class)->getMock();

        $mockGuzzle->expects($this->once())
            ->method('request')
            ->with('GET')
            ->will($this->returnValue($mockResponse));

        $updater = new Updater($mockGuzzle);

        $this->assertEquals($expected, $updater->getAllData());
    }
}