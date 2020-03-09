<?php

namespace App\Tests\Rate;

use App\Rate\RateDataFinder;
use App\Rate\Updater;
use App\Factories\RateModelFactory;
use App\Models\Rate as RateModel;

require_once (__DIR__ . '/RateModelFake.php');

class RateDataFinderTest extends \TestCase
{
    public function testGetRateForFromMatching()
    {
        $from = 'GBP';
        $to = 'GBP';
        $rateValue = 1; // 1 GBP == 1 GBP
        $isFromCache = false; // not hitting the cache

        $rateDataFinder = new RateDataFinder(
            new RateModelFake(),
            $this->createUpdater(),
            $this->createMockRateModelFactory());

        $rateDataFinderReturned = $rateDataFinder->getRateForFromAndTo(
            $from,
            $to
        );

        $this->assertEquals(
            [$rateValue, $isFromCache],
            $rateDataFinderReturned
        );
    }

    public function testGetRateForFromAndToFoundAlive()
    {
        $from = 'USD';
        $to = 'GBP';
        $rateValue = 2; // 1 USD == 2 GBP
        $isFromCache = true; // spoofing a cache hit alive

        $rateModelEntityNormal = new RateModel();
        $rateModelEntityNormal->rate = $rateValue;
        $rateModelEntityNormal->forceAliveOrDead = true;
        $fakeRateModel = $this->createMockFakeQuery(
            $from,
            $to,
            $rateModelEntityNormal
        );

        $rateDataFinder = new RateDataFinder(
            $fakeRateModel,
            $this->createUpdater(),
            $this->createMockRateModelFactory());

        $rateDataFinderReturned = $rateDataFinder->getRateForFromAndTo(
            $from,
            $to
        );

        $this->assertEquals(
            [$rateValue, $isFromCache],
            $rateDataFinderReturned
        );
    }

    public function testGetRateForFromAndToFoundNoneReverseAlive()
    {
        $from = 'USD';
        $to = 'GBP';
        $rateValue = 2; // 1 USD == 2 GBP
        $isFromCache = true; // spoofing a cache hit alive

        $rateModelEntityReverse = new RateModel();
        $rateModelEntityReverse->rate = 0.5;
        $rateModelEntityReverse->forceAliveOrDead = true;
        $fakeRateModel = $this->createMockFakeQuery(
            $from,
            $to,
            null,
            $rateModelEntityReverse
        );

        $rateDataFinder = new RateDataFinder(
            $fakeRateModel,
            $this->createUpdater(),
            $this->createMockRateModelFactory());

        $rateDataFinderReturned = $rateDataFinder->getRateForFromAndTo(
            $from,
            $to
        );

        $this->assertEquals(
            [$rateValue, $isFromCache],
            $rateDataFinderReturned
        );
    }

    public function testGetRateForFromAndToFoundNoneReverseNone()
    {
        $from = 'USD';
        $to = 'GBP';
        $rateValue = 2.0; // 1 USD == 2 GBP (float)
        $isFromCache = false; // missed the cache
        $fakeRateModel = $this->createMockFakeQuery($from, $to, null, null);

        $mockUpdater = $this->createUpdater();
        $mockUpdater->expects($this->once())
            ->method('getAllData')
            ->will($this->returnValue([
                'rates' => [
                    'GBP' => 10,
                    'USD' => 5
                ],
                'base' => 'EUR'
            ]));

        $rateDataFinder = new RateDataFinder(
            $fakeRateModel,
            $mockUpdater,
            $this->createMockRateModelFactory());

        $rateDataFinderReturned = $rateDataFinder->getRateForFromAndTo(
            $from,
            $to
        );

        $this->assertEquals(
            [$rateValue, $isFromCache],
            $rateDataFinderReturned
        );
    }

    public function testGetRateForFromAndToFoundNoneReverseDead()
    {
        $from = 'USD';
        $to = 'GBP';
        $rateValue = 2.0; // 1 USD == 2 GBP (float)
        $isFromCache = false; // missed the cache

        $rateModelEntityReverse = new RateModel();
        $rateModelEntityReverse->forceAliveOrDead = false;

        $fakeRateModel = $this->createMockFakeQuery(
            $from,
            $to,
            null,
            $rateModelEntityReverse);

        $mockUpdater = $this->createUpdater();
        $mockUpdater->expects($this->once())
            ->method('getAllData')
            ->will($this->returnValue([
                'rates' => [
                    'GBP' => 10,
                    'USD' => 5
                ],
                'base' => 'EUR'
            ]));

        $rateDataFinder = new RateDataFinder(
            $fakeRateModel,
            $mockUpdater,
            $this->createMockRateModelFactory());

        $rateDataFinderReturned = $rateDataFinder->getRateForFromAndTo(
            $from,
            $to
        );

        $this->assertEquals(
            [$rateValue, $isFromCache],
            $rateDataFinderReturned
        );
    }

    public function testGetRateForFromAndToFoundDeadReverseAlive()
    {
        $from = 'USD';
        $to = 'GBP';
        $rateValue = 2; // 1 USD == 2 GBP
        $isFromCache = true; // spoofing a cache hit alive

        $rateModelEntityNormal = new RateModel();
        $rateModelEntityNormal->rate = 2;
        $rateModelEntityNormal->forceAliveOrDead = false;

        $rateModelEntityReverse = new RateModel();
        $rateModelEntityReverse->rate = 0.5;
        $rateModelEntityReverse->forceAliveOrDead = true;

        $fakeRateModel = $this->createMockFakeQuery(
            $from,
            $to,
            $rateModelEntityNormal,
            $rateModelEntityReverse);

        $rateDataFinder = new RateDataFinder(
            $fakeRateModel,
            $this->createUpdater(),
            $this->createMockRateModelFactory());

        $rateDataFinderReturned = $rateDataFinder->getRateForFromAndTo(
            $from,
            $to
        );

        $this->assertEquals(
            [$rateValue, $isFromCache],
            $rateDataFinderReturned
        );
    }

    private function createMockFakeQuery(string $from, string $to,
                                   $rateModelEntityNormal = false,
                                   $rateModelEntityReverse = false
                                   ): RateModel
    {
        $queries = [
            $this->createMockQuery($rateModelEntityNormal),
            $this->createMockQuery($rateModelEntityReverse)
        ];

        $fromToCalls = 0;
        // Want to check that the query is good
        $fromToReturns = function ($args) use ($queries,
                                               $from,
                                               $to,
                                               &$fromToCalls)
        {    
            if ($fromToCalls == 0) {
                $this->assertEquals([$from, $to], $args);
                $return = $queries[$fromToCalls];
            } elseif ($fromToCalls == 1) {
                $this->assertEquals([$to, $from], $args);
            }

            $return = $queries[$fromToCalls];
            $fromToCalls++;
            return $return;
        };

        $fakeRateModel = new RateModelFake();
        $fakeRateModel::$fromToReturnsCallback = $fromToReturns;
        return $fakeRateModel;
    }

    private function createMockRateModelFactory(): RateModelFactory
    {
        return $this->createStub(RateModelFactory::class);
    }

    private function createUpdater(): Updater
    {
        return $this->createStub(Updater::class);
    }

    private function createMockQuery($firstReturn = false)
    {
        $mockQuery = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['first'])
            ->getMock();

        if ($firstReturn !== false) {
            $mockQuery->expects($this->once())
                ->method('first')
                ->will($this->returnValue($firstReturn));
        }

        return $mockQuery;
    }
}