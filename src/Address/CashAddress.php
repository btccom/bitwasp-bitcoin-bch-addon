<?php

namespace Btccom\BitcoinCash\Address;

use Btccom\BitcoinCash\Exception\BitcoinCashNetworkRequiredException;
use Btccom\BitcoinCash\Exception\UnsupportedCashAddressException;
use BitWasp\Bitcoin\Address\Address;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Address\ScriptHashAddress;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\ScriptType;
use BitWasp\Buffertools\BufferInterface;
use Btccom\BitcoinCash\Network\BitcoinCashNetworkInterface;

class CashAddress extends Address implements CashAddressInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var BufferInterface
     */
    protected $hash;

    /**
     * CashAddress constructor.
     * @param string $type
     * @param BufferInterface $hash
     * @throws UnsupportedCashAddressException
     */
    public function __construct($type, BufferInterface $hash)
    {
        if ($type !== ScriptType::P2PKH && $type !== ScriptType::P2SH) {
            throw new UnsupportedCashAddressException("Invalid type for bitcoin cash address");
        }

        $this->type = $type;

        parent::__construct($hash);
    }

    /**
     * @param BitcoinCashNetworkInterface $network
     * @return string
     */
    public function getPrefix(BitcoinCashNetworkInterface $network)
    {
        return $network->getCashAddressPrefix();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param NetworkInterface|null $network
     * @return string
     * @throws \CashAddr\Exception\Base32Exception
     * @throws \CashAddr\Exception\CashAddressException
     * @throws BitcoinCashNetworkRequiredException
     */
    public function getAddress(NetworkInterface $network = null)
    {
        if (null === $network) {
            $network = Bitcoin::getNetwork();
        }

        if (!($network instanceof BitcoinCashNetworkInterface)) {
            throw new BitcoinCashNetworkRequiredException("Invalid network - must implement BitcoinCashNetworkInterface");
        }

        return \CashAddr\CashAddress::encode(
            $network->getCashAddressPrefix(),
            $this->type,
            $this->hash->getBinary()
        );
    }

    /**
     * @return PayToPubKeyHashAddress|ScriptHashAddress
     */
    public function getLegacyAddress()
    {
        if ($this->type === ScriptType::P2PKH) {
            return new PayToPubKeyHashAddress($this->hash);
        } else {
            return new ScriptHashAddress($this->hash);
        }
    }

    /**
     * @return \BitWasp\Bitcoin\Script\ScriptInterface
     */
    public function getScriptPubKey()
    {
        if ($this->type === ScriptType::P2PKH) {
            return ScriptFactory::scriptPubKey()->p2pkh($this->hash);
        } else {
            return ScriptFactory::scriptPubKey()->p2sh($this->hash);
        }
    }
}
