<?php

namespace SD\Tests\Gearman\Transport;

use PHPUnit\Framework\TestCase;
use SD\Gearman\Transport\Connection;
use SD\Gearman\Transport\GearmanReceiver;
use SD\Tests\Gearman\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GearmanReceiverTest extends TestCase
{
    public function testGet()
    {
        $receiver = new GearmanReceiver(
            $connection = $this->createMock(Connection::class),
            $serializer = $this->createMock(SerializerInterface::class)
        );

        $connection->method('get')->willReturn(['body' => 'my-body', 'headers' => []]);
        $serializer->method('decode')->with(['body' => 'my-body', 'headers' => []])
            ->willReturn($expectedEnvelope = Envelope::wrap(new DummyMessage('message')));

        $this->assertEquals([$expectedEnvelope], $receiver->get());
    }
}
