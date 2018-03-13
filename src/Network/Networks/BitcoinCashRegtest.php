<?php

namespace Btccom\BitcoinCash\Network\Networks;

class BitcoinCashRegtest extends BitcoinCashTestnet
{
    /**
     * @var string
     */
    protected $cashAddressPrefix = 'bchreg';

    /**
     * {@inheritdoc}
     * @see Network::$p2pMagic
     */
    protected $p2pMagic = "dab5bffa";
}
