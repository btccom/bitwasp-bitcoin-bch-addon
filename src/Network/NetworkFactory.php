<?php

namespace Btccom\BitcoinCash\Network;

class NetworkFactory
{
    /**
     * @return Networks\BitcoinCash
     * @throws \Exception
     */
    public static function bitcoinCash()
    {
        return new Networks\BitcoinCash();
    }

    /**
     * @return Networks\BitcoinCashTestnet
     * @throws \Exception
     */
    public static function bitcoinCashTestnet()
    {
        return new Networks\BitcoinCashTestnet();
    }

    /**
     * @return Networks\BitcoinCashRegtest
     * @throws \Exception
     */
    public static function bitcoinCashRegtest()
    {
        return new Networks\BitcoinCashRegtest();
    }
}
