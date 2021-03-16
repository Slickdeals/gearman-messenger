<?php

namespace SD\Tests\Gearman\Transport;

use PHPUnit\Framework\TestCase;
use SD\Gearman\Transport\Connection;
use SD\Gearman\Transport\GearmanStamp;
use SD\Gearman\Transport\GearmanTransport;
use SD\Tests\Gearman\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GearmanTransportTest extends TestCase
{
    public function testReceivesMessages()
    {
        $transport = new GearmanTransport(
            $connection = $this->createMock(Connection::class),
            $serializer = $this->createMock(SerializerInterface::class)
        );

        $decodedMessage = new DummyMessage('decoded');

        $gearmanEnvelope = [
            'body' => 'body',
        ];

        $serializer->method('decode')->with(['body' => 'body'])->willReturn(new Envelope($decodedMessage));
        $connection->method('get')->willReturn($gearmanEnvelope);

        $envelopes = $transport->get();
        $this->assertSame($decodedMessage, $envelopes[0]->getMessage());
    }

    public function testSendsMessages()
    {
        $transport = new GearmanTransport(
            $connection = $this->createMock(Connection::class),
            $serializer = $this->createMock(SerializerInterface::class)
        );

        $envelope = new Envelope(new DummyMessage('dummy'), [new GearmanStamp('my-function')]);

        $serializer->method('encode')->with($envelope->withoutStampsOfType(GearmanStamp::class))->willReturn(['body' => 'serialized-body']);
        $connection->method('send')->with('my-function', 'serialized-body');

        $returnedEnvelope = $transport->send($envelope);

        $this->assertSame($envelope, $returnedEnvelope);
    }
}
