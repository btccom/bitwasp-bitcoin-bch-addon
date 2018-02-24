<?php

namespace Btccom\BitcoinCash\Test\Address;

use Btccom\BitcoinCash\Address\CashAddress;
use Btccom\BitcoinCash\Exception\BitcoinCashNetworkRequiredException;
use Btccom\BitcoinCash\Exception\UnsupportedCashAddressException;
use Btccom\BitcoinCash\Network\BitcoinCashNetworkInterface;
use Btccom\BitcoinCash\Test\AbstractTestCase;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Script\ScriptType;
use BitWasp\Buffertools\Buffer;

class CashAddressTest extends AbstractTestCase
{
    /**
     * @dataProvider \Btccom\BitcoinCash\Test\Address\AddressCreatorTest::getCashAddressesAndNetwork()
     * @param BitcoinCashNetworkInterface $network
     * @param string $addressStr
     * @param string $addrScriptType
     * @param string $hashHex
     * @throws \CashAddr\Exception\Base32Exception
     * @throws \CashAddr\Exception\CashAddressException
     * @throws \Exception
     */
    public function testCashAddress(BitcoinCashNetworkInterface $network, $addressStr, $addrScriptType, $hashHex)
    {
        $address = new CashAddress($addrScriptType, Buffer::hex($hashHex));
        if (substr($addressStr, 0, strlen($network->getCashAddressPrefix())) === $network->getCashAddressPrefix()) {
            $this->assertEquals($addressStr, $address->getAddress($network));
        } else {
            $this->assertEquals($network->getCashAddressPrefix() . ":" . $addressStr, $address->getAddress($network));
        }

        $this->assertEquals($network->getCashAddressPrefix(), $address->getPrefix($network));
    }

    public function testConstructorRequiresValidScriptType()
    {
        $this->expectException(UnsupportedCashAddressException::class);
        new CashAddress(ScriptType::P2PK, new Buffer());
    }

    public function testGetAddressAbortsInvalidNetwork()
    {
        $address = new CashAddress(ScriptType::P2SH, Buffer::hex("", 20));

        $this->expectException(BitcoinCashNetworkRequiredException::class);
        $this->expectExceptionMessage("Invalid network - must implement BitcoinCashNetworkInterface");

        $address->getAddress(NetworkFactory::litecoin());
    }

    public function testGetAddressAbortsInvalidDefaultNetwork()
    {
        $prevDefault = Bitcoin::getNetwork();
        Bitcoin::setNetwork(NetworkFactory::litecoin());
        $address = new CashAddress(ScriptType::P2SH, Buffer::hex("", 20));

        $e = null;
        try {
            $address->getAddress();
        } catch (BitcoinCashNetworkRequiredException $_e) {
            $e = $_e;
            $this->assertEquals("Invalid network - must implement BitcoinCashNetworkInterface", $e->getMessage());
        } finally {
            Bitcoin::setNetwork($prevDefault);
        }

        $this->assertInstanceOf(BitcoinCashNetworkRequiredException::class, $e);
    }
}
