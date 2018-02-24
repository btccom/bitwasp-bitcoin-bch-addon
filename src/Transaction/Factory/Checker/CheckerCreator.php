<?php

namespace Btccom\BitcoinCash\Transaction\Factory\Checker;

use BitWasp\Bitcoin\Crypto\EcAdapter\Adapter\EcAdapterInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\EcSerializer;
use BitWasp\Bitcoin\Crypto\EcAdapter\Serializer\Key\PublicKeySerializerInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Serializer\Signature\DerSignatureSerializerInterface;
use BitWasp\Bitcoin\Script\Interpreter\CheckerBase;
use Btccom\BitcoinCash\Script\Interpreter\BitcoinCashChecker;
use BitWasp\Bitcoin\Serializer\Signature\TransactionSignatureSerializer;
use BitWasp\Bitcoin\Transaction\Factory\Checker\CheckerCreatorBase;
use BitWasp\Bitcoin\Transaction\TransactionInterface;
use BitWasp\Bitcoin\Transaction\TransactionOutputInterface;

class CheckerCreator extends CheckerCreatorBase
{
    /**
     * @param EcAdapterInterface $ecAdapter
     * @return CheckerCreator
     */
    public static function fromEcAdapter(EcAdapterInterface $ecAdapter)
    {
        return new self(
            $ecAdapter,
            new TransactionSignatureSerializer(EcSerializer::getSerializer(DerSignatureSerializerInterface::class, true, $ecAdapter)),
            EcSerializer::getSerializer(PublicKeySerializerInterface::class, true, $ecAdapter)
        );
    }

    /**
     * @param TransactionInterface $tx
     * @param int $nInput
     * @param TransactionOutputInterface $txOut
     * @return CheckerBase
     */
    public function create(TransactionInterface $tx, $nInput, TransactionOutputInterface $txOut)
    {
        return new BitcoinCashChecker(
            $this->ecAdapter,
            $tx,
            $nInput,
            $txOut->getValue(),
            $this->txSigSerializer,
            $this->pubKeySerializer
        );
    }
}
