<?php

namespace SD\Gearman\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GearmanSender implements SenderInterface
{
    private $connection;
    private $serializer;

    public function __construct(Connection $connection, SerializerInterface $serializer)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    public function send(Envelope $envelope): Envelope
    {
        /** @var GearmanStamp $gearmanStamp */
        $gearmanStamp = $envelope->last(GearmanStamp::class);

        if (!$gearmanStamp) {
            throw new TransportException('GearmanStamp required on envelope.');
        }

        $encodedMessage = $this->serializer->encode($envelope);

        $this->connection->send($gearmanStamp->getFunction(), $encodedMessage['body'], $encodedMessage['headers'] ?? []);

        return $envelope;
    }
}
