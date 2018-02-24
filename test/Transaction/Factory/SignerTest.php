<?php

namespace Btccom\BitcoinCash\Test\Transaction\Factory;

use Btccom\BitcoinCash\Address\AddressCreator;
use Btccom\BitcoinCash\Network\Networks\BitcoinCashTestnet;
use Btccom\BitcoinCash\Test\AbstractTestCase;
use Btccom\BitcoinCash\Transaction\Factory\Checker\CheckerCreator as BitcoinCashCheckerCreator;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Key\PrivateKeyFactory;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Transaction\Factory\Signer;
use BitWasp\Bitcoin\Transaction\Factory\TxBuilder;
use BitWasp\Bitcoin\Transaction\SignatureHash\SigHash;
use Btccom\BitcoinCash\Transaction\SignatureHash\SigHash as BchSigHash;
use BitWasp\Bitcoin\Transaction\TransactionOutput;

class SignerTest extends AbstractTestCase
{
    public function testBitcoinCash()
    {
        $expectSpend = '020000000113aaf49280ba92bddfcbdc30d6c7501c2575e4a80f539236df233f9218a2c8400000000049483045022100c5874e39da4dd427d35e24792bf31dcd63c25684deec66b426271b4043e21c3002201bfdc0621ad4237e8db05aa6cad69f3d5ab4ae32ebb2048f65b12165da6cc69341ffffffff0100f2052a010000001976a914cd29cc97826c37281ac61301e4d5ed374770585688ac00000000';
        $value = 50 * 100000000;

        $txid = "40c8a218923f23df3692530fa8e475251c50c7d630dccbdfbd92ba8092f4aa13";
        $vout = 0;
        $network = new BitcoinCashTestnet();

        $wif = "cTNwkxh7nVByhc3i7BH6eaBFQ4yVs6WvXBGBoA9xdKiorwcYVACc";
        $keyPair = PrivateKeyFactory::fromWif($wif, null, $network);

        $spk = ScriptFactory::scriptPubKey()->payToPubKey($keyPair->getPublicKey());
        $addressCreator = new AddressCreator(true);
        $dest = $addressCreator->fromString('mzDktdwPcWwqg8aZkPotx6aYi4mKvDD7ay', $network)->getScriptPubKey();

        $txb = (new TxBuilder())
            ->version(2)
            ->input($txid, $vout)
            ->output($value, $dest)
        ;

        $ecAdapter = Bitcoin::getEcAdapter();
        $checkerCreator = BitcoinCashCheckerCreator::fromEcAdapter($ecAdapter);

        $txs = new Signer($txb->get());
        $txs->setCheckerCreator($checkerCreator);

        $hashType = BchSigHash::BITCOINCASH | SigHash::ALL;

        $input = $txs->input(0, new TransactionOutput($value, $spk));
        $input->sign($keyPair, $hashType);

        $tx = $txs->get();
        $this->assertEquals($expectSpend, $tx->getHex());
    }
}
