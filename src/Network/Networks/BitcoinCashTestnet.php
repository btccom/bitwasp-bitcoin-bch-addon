<?php

namespace Btccom\BitcoinCash\Network\Networks;

use Btccom\BitcoinCash\Network\Networks\AbstractBitcoinCash;
use BitWasp\Bitcoin\Network\NetworkFactory;

class BitcoinCashTestnet extends AbstractBitcoinCash
{
    /**
     * BitcoinCash constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct(
            NetworkFactory::bitcoinTestnet(),
            "bchtest"
        );
    }
}
