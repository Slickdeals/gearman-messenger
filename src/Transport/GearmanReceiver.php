<?php

namespace SD\Gearman\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GearmanReceiver implements ReceiverInterface
{
    private $connection;
    private $serializer;

    public function __construct(Connection $connection, SerializerInterface $serializer)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    public function get(): iterable
    {
        $message = $this->connection->get();

        if (null === $message) {
            return [];
        }

        $envelope = $this->serializer->decode($message);

        return [$envelope];
    }

    public function ack(Envelope $envelope): void
    {
        // No ack needed for Gearman
    }

    public function reject(Envelope $envelope): void
    {
        // Nothing to do here
    }
}
