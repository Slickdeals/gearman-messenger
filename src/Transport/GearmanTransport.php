<?php

namespace SD\Gearman\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class GearmanTransport implements TransportInterface
{
    private $connection;
    private $serializer;
    private $receiver;
    private $sender;

    public function __construct(Connection $connection, SerializerInterface $serializer)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    public function get(): iterable
    {
        return ($this->receiver ?? $this->getReceiver())->get();
    }

    public function ack(Envelope $envelope): void
    {
        ($this->receiver ?? $this->getReceiver())->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        ($this->receiver ?? $this->getReceiver())->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return ($this->sender ?? $this->getSender())->send($envelope);
    }

    private function getReceiver(): GearmanReceiver
    {
        return $this->receiver = new GearmanReceiver($this->connection, $this->serializer);
    }

    private function getSender(): GearmanSender
    {
        return $this->sender = new GearmanSender($this->connection, $this->serializer);
    }
}
