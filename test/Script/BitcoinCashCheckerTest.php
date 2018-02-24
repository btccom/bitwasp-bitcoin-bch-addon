<?php

namespace Btccom\BitcoinCash\Test\Script;

use Btccom\BitcoinCash\Script\Interpreter\BitcoinCashChecker;
use Btccom\BitcoinCash\Test\AbstractTestCase;
use Btccom\BitcoinCash\Transaction\SignatureHash\SigHash as BchSigHash;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Script\Script;
use BitWasp\Bitcoin\Transaction\OutPoint;
use BitWasp\Bitcoin\Transaction\Transaction;
use BitWasp\Bitcoin\Transaction\TransactionInput;
use BitWasp\Bitcoin\Transaction\TransactionOutput;
use BitWasp\Buffertools\Buffer;

class BitcoinCashCheckerTest extends AbstractTestCase
{
    public function testRejectsSigVersionNonZero()
    {
        $tx = new Transaction(
            1,
            [new TransactionInput(new OutPoint(Buffer::hex('', 32), 0xffffffff), new Script(), 0xffffffff)]
        );

        $txOut = new TransactionOutput(5000000000, new Script());
        $adapter = Bitcoin::getEcAdapter();
        $checker = new BitcoinCashChecker($adapter, $tx, 0, 0);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("SigVersion must be 0");

        $sigVersion = 1;
        $hashType = BchSigHash::BITCOINCASH | BchSigHash::ALL;
        $checker->getSigHash($txOut->getScript(), $hashType, $sigVersion);
    }

    public function testOldFormatStillAllowed()
    {
        $tx = new Transaction(
            1,
            [new TransactionInput(new OutPoint(Buffer::hex('', 32), 0xffffffff), new Script(), 0xffffffff)]
        );

        $txOut = new TransactionOutput(5000000000, new Script());
        $adapter = Bitcoin::getEcAdapter();
        $checker = new BitcoinCashChecker($adapter, $tx, 0, 0);

        $sigVersion = 0;
        $replayProtectedFlags = BchSigHash::BITCOINCASH | BchSigHash::ALL;
        $replayProtectedSigHash = $checker->getSigHash($txOut->getScript(), $replayProtectedFlags, $sigVersion);
        $this->assertEquals(
            "90dce594295753a9d8109ba2086ca54840ab314b947d507f07e00a3e5741768f",
            $replayProtectedSigHash->getHex()
        );

        $normalSigHash = $checker->getSigHash($txOut->getScript(), BchSigHash::ALL, $sigVersion);
        $this->assertEquals(
            "f4f430503c7e0815d14eaf2c24438fac3bd7c750d9276d4a30ed64d1a96343b7",
            $normalSigHash->getHex()
        );

        $this->assertFalse($replayProtectedSigHash->equals($normalSigHash));
    }

    public function testRepeatedCallsAreTheSame()
    {
        // mostly for coverage
        $tx = new Transaction(
            1,
            [new TransactionInput(new OutPoint(Buffer::hex('', 32), 0xffffffff), new Script(), 0xffffffff)]
        );

        $txOut = new TransactionOutput(5000000000, new Script());
        $adapter = Bitcoin::getEcAdapter();
        $checker = new BitcoinCashChecker($adapter, $tx, 0, 0);

        $sigVersion = 0;
        $replayProtectedFlags = BchSigHash::BITCOINCASH | BchSigHash::ALL;
        $sh1 = $checker->getSigHash($txOut->getScript(), $replayProtectedFlags, $sigVersion);
        $sh2 = $checker->getSigHash($txOut->getScript(), $replayProtectedFlags, $sigVersion);

        $this->assertTrue($sh1->equals($sh2));
    }
}
