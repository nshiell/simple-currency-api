<?php

use App\Factories\RateFactory;
use App\Rate\Rate;
use App\Rate\RateDataFinder;

class RateFactoryTest extends TestCase
{
    public function testCreateRateBadFrom()
    {
        $allowedCurrencies = ['GBP'];
        /** @var RateDataFinder */
        $mockRateDataFinder = $this->createStub(RateDataFinder::class);

        $this->expectException(\UnexpectedValueException::class);
        $rateFactory = new RateFactory(
            $mockRateDataFinder,
            $allowedCurrencies
        );
        
        $rateFactory->createRate('XXX', 'GBP');
    }

    public function testCreateRateBadTo()
    {
        $allowedCurrencies = ['GBP'];
        /** @var RateDataFinder */
        $mockRateDataFinder = $this->createStub(RateDataFinder::class);

        $this->expectException(\UnexpectedValueException::class);
        $rateFactory = new RateFactory(
            $mockRateDataFinder,
            $allowedCurrencies
        );
        
        $rateFactory->createRate('GBP', 'XXX');
    }

    public function testCreateRate()
    {
        $allowedCurrencies = ['GBP', 'CAD'];

        /** @var RateDataFinder */
        $mockRateDataFinder = $this->createStub(RateDataFinder::class);
        $mockRateDataFinder->method('getRateForFromAndTo')
            ->willReturn([123, false]);

        $rateFactory = new RateFactory($mockRateDataFinder, $allowedCurrencies);
        $rate = $rateFactory->createRate('GBP', 'CAD');

        $this->assertInstanceOf(Rate::class, $rate);
        $this->assertEquals(123, $rate->getValueExchanged(1));
        $this->assertEquals(false, $rate->getIsFromCache(1));
    }
}
