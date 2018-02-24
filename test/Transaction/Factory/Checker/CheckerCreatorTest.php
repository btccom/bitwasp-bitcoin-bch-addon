<?php

namespace Btccom\BitcoinCash\Test\Transaction\Factory\Checker;

use Btccom\BitcoinCash\Script\Interpreter\BitcoinCashChecker;
use Btccom\BitcoinCash\Test\AbstractTestCase;
use Btccom\BitcoinCash\Transaction\Factory\Checker\CheckerCreator;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Script\Script;
use BitWasp\Bitcoin\Transaction\OutPoint;
use BitWasp\Bitcoin\Transaction\Transaction;
use BitWasp\Bitcoin\Transaction\TransactionInput;
use BitWasp\Bitcoin\Transaction\TransactionOutput;
use BitWasp\Buffertools\Buffer;

class CheckerCreatorTest extends AbstractTestCase
{
    public function testMakesBitcoinCashChecker()
    {
        $tx = new Transaction(
            1,
            [new TransactionInput(new OutPoint(Buffer::hex('', 32), 0xffffffff), new Script(), 0xffffffff)]
        );

        $txOut = new TransactionOutput(5000000000, new Script());

        $adapter = Bitcoin::getEcAdapter();
        $factory = CheckerCreator::fromEcAdapter($adapter);
        $checker = $factory->create($tx, 0, $txOut);

        $this->assertInstanceOf(BitcoinCashChecker::class, $checker);
    }
}
