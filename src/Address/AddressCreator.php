<?php

namespace Btccom\BitcoinCash\Address;

use BitWasp\Bitcoin\Address\Base58Address;
use BitWasp\Bitcoin\Address\Base58AddressInterface;
use BitWasp\Bitcoin\Address\BaseAddressCreator;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Address\ScriptHashAddress;
use BitWasp\Bitcoin\Base58;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Exceptions\UnrecognizedAddressException;
use BitWasp\Bitcoin\Exceptions\UnrecognizedScriptForAddressException;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Script\Classifier\OutputClassifier;
use BitWasp\Bitcoin\Script\ScriptInterface;
use BitWasp\Bitcoin\Script\ScriptType;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use Btccom\BitcoinCash\Network\BitcoinCashNetworkInterface;

class AddressCreator extends BaseAddressCreator
{
    /**
     * @var bool
     */
    private $useNewCashAddress;

    /**
     * BitcoinCashAddressReader constructor.
     * @param bool $useNewCashAddress
     */
    public function __construct($useNewCashAddress = true)
    {
        $this->useNewCashAddress = $useNewCashAddress;
    }

    /**
     * @param string $strAddress
     * @param NetworkInterface $network
     * @return Base58Address|null
     */
    private function readBase58Address($strAddress, NetworkInterface $network)
    {
        try {
            $data = Base58::decodeCheck($strAddress);
            $prefixByte = $data->slice(0, 1)->getHex();

            if ($prefixByte === $network->getP2shByte()) {
                return new ScriptHashAddress($data->slice(1));
            } else if ($prefixByte === $network->getAddressByte()) {
                return new PayToPubKeyHashAddress($data->slice(1));
            }
        } catch (\Exception $e) {
            // Just return null
        }

        return null;
    }

    /**
     * @param string $strAddress
     * @param BitcoinCashNetworkInterface $network
     * @return CashAddress|null
     */
    private function readCashAddress($strAddress, BitcoinCashNetworkInterface $network)
    {
        try {
            list ($prefix, $scriptType, $hash) = \CashAddr\CashAddress::decode($strAddress);
            if ($prefix !== $network->getCashAddressPrefix()) {
                return null;
            }

            return new CashAddress($scriptType, new Buffer($hash, 20));
        } catch (\Exception $e) {
            // continue on
        }

        return null;
    }

    /**
     * @param string $strAddress
     * @param NetworkInterface|null $network
     * @return Base58AddressInterface|CashAddress
     * @throws UnrecognizedAddressException
     */
    public function fromString($strAddress, NetworkInterface $network = null)
    {
        $network = $network ?: Bitcoin::getNetwork();

        if (($base58Address = $this->readBase58Address($strAddress, $network))) {
            return $base58Address;
        }

        if ($this->useNewCashAddress && $network instanceof BitcoinCashNetworkInterface) {
            if (($base32Address = $this->readCashAddress(strtolower($strAddress), $network))) {
                return $base32Address;
            }

            if (($base32Address = $this->readCashAddress(
                sprintf("%s:%s", $network->getCashAddressPrefix(), strtolower($strAddress)),
                $network
            ))) {
                return $base32Address;
            }
        }

        throw new UnrecognizedAddressException("Address not recognized");
    }

    /**
     * @param ScriptInterface $script
     * @return Base58AddressInterface|CashAddress
     * @throws UnrecognizedScriptForAddressException
     */
    public function fromOutputScript(ScriptInterface $script)
    {
        $decode = (new OutputClassifier())->decode($script);

        switch ($decode->getType()) {
            case ScriptType::P2PKH:
                /** @var BufferInterface $solution */
                if ($this->useNewCashAddress) {
                    return new CashAddress(ScriptType::P2PKH, $decode->getSolution());
                } else {
                    return new PayToPubKeyHashAddress($decode->getSolution());
                }
                break;
            case ScriptType::P2SH:
                /** @var BufferInterface $solution */
                if ($this->useNewCashAddress) {
                    return new CashAddress(ScriptType::P2SH, $decode->getSolution());
                } else {
                    return new ScriptHashAddress($decode->getSolution());
                }
                break;
            default:
                throw new UnrecognizedScriptForAddressException('Script type is not associated with an address');
        }
    }
}
