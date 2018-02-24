<?php

namespace Btccom\BitcoinCash\Test\Network\Networks;

use Btccom\BitcoinCash\Network\Networks\BitcoinCashTestnet;
use Btccom\BitcoinCash\Test\AbstractTestCase;

class BitcoinCashTestnetTest extends AbstractTestCase
{
    public function testBitcoinCashTestnet()
    {
        $network = new BitcoinCashTestnet();
        $this->assertEquals('6f', $network->getAddressByte());
        $this->assertEquals('c4', $network->getP2shByte());
        $this->assertEquals('ef', $network->getPrivByte());
        $this->assertEquals('04358394', $network->getHDPrivByte());
        $this->assertEquals('043587cf', $network->getHDPubByte());
        $this->assertEquals('0709110b', $network->getNetMagicBytes());
        $this->assertEquals('bchtest', $network->getCashAddressPrefix());
    }
}
