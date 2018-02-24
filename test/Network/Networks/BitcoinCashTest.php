<?php

namespace Btccom\BitcoinCash\Test\Network\Networks;

use Btccom\BitcoinCash\Network\Networks\BitcoinCash;
use Btccom\BitcoinCash\Test\AbstractTestCase;

class BitcoinCashTest extends AbstractTestCase
{
    public function testBitcoinCash()
    {
        $network = new BitcoinCash();
        $this->assertEquals('00', $network->getAddressByte());
        $this->assertEquals('05', $network->getP2shByte());
        $this->assertEquals('80', $network->getPrivByte());
        $this->assertEquals('0488ade4', $network->getHDPrivByte());
        $this->assertEquals('0488b21e', $network->getHDPubByte());
        $this->assertEquals('d9b4bef9', $network->getNetMagicBytes());
        $this->assertEquals('bitcoincash', $network->getCashAddressPrefix());
    }
}
