<?php

namespace SD\Tests\Gearman\Transport;

use PHPUnit\Framework\TestCase;
use SD\Gearman\Transport\Connection;
use SD\Gearman\Transport\GearmanSender;
use SD\Gearman\Transport\GearmanStamp;
use SD\Tests\Gearman\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GearmanSenderTest extends TestCase
{
    public function testStampRequired()
    {
        $envelope = Envelope::wrap(new DummyMessage('foo'));
        $sender = new GearmanSender($this->createMock(Connection::class), $this->createMock(SerializerInterface::class));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('GearmanStamp required on envelope.');

        $sender->send($envelope);
    }

    public function testSends()
    {
        $envelope = Envelope::wrap(new DummyMessage('foo'), [new GearmanStamp('my-function')]);
        $sender = new GearmanSender(
            $connection = $this->createMock(Connection::class),
            $serializer = $this->createMock(SerializerInterface::class)
        );
        $envelopeWithoutDestination = $envelope->withoutStampsOfType(GearmanStamp::class);

        $serializer->method('encode')->with($envelopeWithoutDestination)->willReturn(['body' => 'serialized-body']);
        $connection->method('send')->with('my-function', 'serialized-body', []);

        $this->assertSame($envelope, $sender->send($envelope));
    }
}
