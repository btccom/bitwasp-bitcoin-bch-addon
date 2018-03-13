<?php

namespace Btccom\BitcoinCash\Test\Address;

use BitWasp\Bitcoin\Network\NetworkInterface;
use Btccom\BitcoinCash\Address\AddressCreator;
use Btccom\BitcoinCash\Address\CashAddress;
use Btccom\BitcoinCash\Network\BitcoinCashNetworkInterface;
use Btccom\BitcoinCash\Network\Networks\BitcoinCash;
use Btccom\BitcoinCash\Network\Networks\BitcoinCashRegtest;
use Btccom\BitcoinCash\Network\Networks\BitcoinCashTestnet;
use Btccom\BitcoinCash\Test\AbstractTestCase;
use BitWasp\Bitcoin\Address\Base58Address;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Address\ScriptHashAddress;
use BitWasp\Bitcoin\Exceptions\UnrecognizedAddressException;
use BitWasp\Bitcoin\Exceptions\UnrecognizedScriptForAddressException;
use BitWasp\Bitcoin\Script\Script;
use BitWasp\Bitcoin\Script\ScriptType;
use BitWasp\Buffertools\Buffer;

class AddressCreatorTest extends AbstractTestCase
{
    private function decodingCashAddrSucceeds(AddressCreator $creator, $address, NetworkInterface $network)
    {
        $decoded = $creator->fromString($address, $network);
        $this->assertInstanceOf(CashAddress::class, $decoded);
    }

    private function decodingCashAddrFails(AddressCreator $creator, $address, NetworkInterface $network)
    {
        $failure = null;
        try {
            $creator->fromString($address, $network);
        } catch (\Exception $e) {
            $failure = $e;
        } finally {
            $this->assertInstanceOf(UnrecognizedAddressException::class, $failure, "decoding cashaddr should not succeed");
        }
    }

    public function testDefaultEnablesCashAddr()
    {
        $network = new BitcoinCash();
        $address = "bitcoincash:qpm2qsznhks23z7629mms6s4cwef74vcwvy22gdx6a";
        $creator = new AddressCreator();
        $this->decodingCashAddrSucceeds($creator, $address, $network);

        $creator = new AddressCreator(true);
        $this->decodingCashAddrSucceeds($creator, $address, $network);

        $creator = new AddressCreator(false);
        $this->decodingCashAddrFails($creator, $address, $network);
    }

    public function testReadsP2SHBase58Address()
    {
        $network = new BitcoinCashTestnet();
        $addressStr = "2N44ThNe8NXHyv4bsX8AoVCXquBRW94Ls7W";
        $reader = new AddressCreator(false);
        $address = $reader->fromString($addressStr, $network);
        $this->assertInstanceOf(Base58Address::class, $address);
        $this->assertEquals($addressStr, $address->getAddress($network));

        $spk = $address->getScriptPubKey();
        $addressAgain = $reader->fromOutputScript($spk);
        $this->assertEquals($addressStr, $addressAgain->getAddress($network));
    }

    public function testReadsP2PKHBase58Address()
    {
        $network = new BitcoinCashTestnet();
        $addressStr = "n4nDp9W2x54oFxdWSHdf4fADLhW7grAHme";
        $reader = new AddressCreator(false);
        $address = $reader->fromString($addressStr, $network);
        $this->assertInstanceOf(Base58Address::class, $address);
        $this->assertEquals($addressStr, $address->getAddress($network));

        $spk = $address->getScriptPubKey();
        $addressAgain = $reader->fromOutputScript($spk);
        $this->assertEquals($addressStr, $addressAgain->getAddress($network));
    }

    public function testRejectsUnrecognizedAddresses()
    {
        $network = new BitcoinCashTestnet();
        $addressStr = "tb1qrp33g0q5c5txsp9arysrx4k6zdkfs4nce4xj0gdcccefvpysxf3q0sl5k7";
        $reader = new AddressCreator(true);

        $this->expectException(UnrecognizedAddressException::class);
        $this->expectExceptionMessage("Address not recognized");

        $reader->fromString($addressStr, $network);
    }

    public function testRejectsCashAddressIfOptOut()
    {
        $network = new BitcoinCash();
        $addressStr = "bitcoincash:qpm2qsznhks23z7629mms6s4cwef74vcwvy22gdx6a";
        $reader = new AddressCreator(false);

        $this->expectException(UnrecognizedAddressException::class);
        $this->expectExceptionMessage("Address not recognized");

        $reader->fromString($addressStr, $network);
    }

    public function testRejectsUnrecognizedScripts()
    {
        $reader = new AddressCreator(true);

        $this->expectException(UnrecognizedScriptForAddressException::class);
        $this->expectExceptionMessage("Script type is not associated with an address");

        $reader->fromOutputScript(new Script(new Buffer("invalid")));
    }

    public function testRejectsAddressWithWrongNetworkPrefix()
    {
        $network = new BitcoinCashTestnet();
        $addressStr = "bitcoincash:qpm2qsznhks23z7629mms6s4cwef74vcwvy22gdx6a";
        $reader = new AddressCreator(true);

        $this->expectException(UnrecognizedAddressException::class);
        $this->expectExceptionMessage("Address not recognized");

        $reader->fromString($addressStr, $network);
    }
    public function testStillReadsBase58AddressWithCashAddress()
    {
        $network = new BitcoinCashTestnet();
        $addressStr = "2N44ThNe8NXHyv4bsX8AoVCXquBRW94Ls7W";
        $reader = new AddressCreator(true);
        $address = $reader->fromString($addressStr, $network);
        $this->assertInstanceOf(Base58Address::class, $address);
        $this->assertEquals($addressStr, $address->getAddress($network));
    }

    public static function getCashAddressesAndNetwork()
    {
        $tbch = new BitcoinCashTestnet();
        $bch = new BitcoinCash();
        $rbch = new BitcoinCashRegtest();

        return [
            [$bch, "bitcoincash:qpm2qsznhks23z7629mms6s4cwef74vcwvy22gdx6a", ScriptType::P2PKH, "76a04053bda0a88bda5177b86a15c3b29f559873"],
            [$bch, "bitcoincash:ppm2qsznhks23z7629mms6s4cwef74vcwvn0h829pq", ScriptType::P2SH, "76a04053bda0a88bda5177b86a15c3b29f559873"],
            [$bch, "ppm2qsznhks23z7629mms6s4cwef74vcwvn0h829pq", ScriptType::P2SH, "76a04053bda0a88bda5177b86a15c3b29f559873"],
            [$tbch, "bchtest:ppm2qsznhks23z7629mms6s4cwef74vcwvhanqgjxu", ScriptType::P2SH, "76a04053bda0a88bda5177b86a15c3b29f559873"],
            [$tbch, "ppm2qsznhks23z7629mms6s4cwef74vcwvhanqgjxu", ScriptType::P2SH, "76a04053bda0a88bda5177b86a15c3b29f559873"],
            [$rbch, "bchreg:pqzg22ty3m437frzk4y0gvvyqj02jpfv7udqugqkne", ScriptType::P2SH, "048529648eeb1f2462b548f43184049ea9052cf7"],
            [$rbch, "pqzg22ty3m437frzk4y0gvvyqj02jpfv7udqugqkne", ScriptType::P2SH, "048529648eeb1f2462b548f43184049ea9052cf7"],
        ];
    }

    /**
     * @dataProvider getCashAddressesAndNetwork
     * @param BitcoinCashNetworkInterface $network
     * @param string $addressStr
     * @param string $addrScriptType
     * @param string $hashHex
     */
    public function testReadsCashAddresses(BitcoinCashNetworkInterface $network, $addressStr, $addrScriptType, $hashHex)
    {
        $reader = new AddressCreator(true);
        $address = $reader->fromString($addressStr, $network);
        $spk = $address->getScriptPubKey();
        $addressFromScriptPubKey = $reader->fromOutputScript($spk);

        $this->assertInstanceOf(CashAddress::class, $address);

        if (substr($addressStr, 0, strlen($network->getCashAddressPrefix())) === $network->getCashAddressPrefix()) {
            $expectedEncoding = $addressStr;
        } else {
            $expectedEncoding = $network->getCashAddressPrefix() . ":" . $addressStr;
        }

        $this->assertEquals($expectedEncoding, $address->getAddress($network));
        $this->assertEquals($expectedEncoding, $addressFromScriptPubKey->getAddress($network));
        $this->assertEquals($addrScriptType, $address->getType());
        $this->assertEquals($hashHex, $address->getHash()->getHex());

        if ($addrScriptType === ScriptType::P2SH) {
            $expected = new ScriptHashAddress($address->getHash());
        } else if ($addrScriptType === ScriptType::P2PKH) {
            $expected = new PayToPubKeyHashAddress($address->getHash());
        } else {
            $this->markTestIncomplete("Need support for more script types in testReadsCashAddresses");
            return;
        }

        $legacy = $address->getLegacyAddress();
        $this->assertEquals(get_class($expected), get_class($legacy), "getLegacyAddress returns correct legacy object");
        $this->assertEquals($expected->getAddress($network), $legacy->getAddress($network), "legacy address encodes to expected value");
    }
}
