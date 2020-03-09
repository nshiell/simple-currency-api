<?php

use App\Rate\Rate;

class RateTest extends TestCase
{
    public function testGetValueExchangedCached()
    {
        $rate = new Rate(2, true);
        $this->assertEquals($rate->getValueExchanged(6), 12);
        $this->assertTrue($rate->getIsFromCache());
    }

    public function testGetValueExchangedNotCached()
    {
        $rate = new Rate(2, false);
        $this->assertEquals($rate->getValueExchanged(6), 12);
        $this->assertFalse($rate->getIsFromCache());
    }
}
