<?php

namespace Btccom\BitcoinCash\Network;

interface BitcoinCashNetworkInterface
{
    /**
     * @return string
     */
    public function getCashAddressPrefix();
}
