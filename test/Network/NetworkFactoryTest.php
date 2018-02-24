<?php

namespace Btccom\BitcoinCash\Test\Network;

use Btccom\BitcoinCash\Network\NetworkFactory;
use Btccom\BitcoinCash\Network\Networks\BitcoinCash;
use Btccom\BitcoinCash\Network\Networks\BitcoinCashRegtest;
use Btccom\BitcoinCash\Network\Networks\BitcoinCashTestnet;
use Btccom\BitcoinCash\Test\AbstractTestCase;

class NetworkFactoryTest extends AbstractTestCase
{
    public function testBitcoinCash()
    {
        $this->assertInstanceOf(
            BitcoinCash::class,
            NetworkFactory::bitcoinCash()
        );
    }

    public function testBitcoinCashTestnet()
    {
        $this->assertInstanceOf(
            BitcoinCashTestnet::class,
            NetworkFactory::bitcoinCashTestnet()
        );
    }

    public function testBitcoinCashRegtest()
    {
        $this->assertInstanceOf(
            BitcoinCashRegtest::class,
            NetworkFactory::bitcoinCashRegtest()
        );
    }
}
