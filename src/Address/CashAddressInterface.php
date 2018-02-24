<?php

namespace Btccom\BitcoinCash\Address;

use BitWasp\Bitcoin\Address\AddressInterface;
use Btccom\BitcoinCash\Network\BitcoinCashNetworkInterface;

interface CashAddressInterface extends AddressInterface
{
    /**
     * @param BitcoinCashNetworkInterface $network
     * @return string
     */
    public function getPrefix(BitcoinCashNetworkInterface $network);
}
