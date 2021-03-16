<?php

namespace SD\Tests\Gearman\Transport;

use PHPUnit\Framework\TestCase;
use SD\Gearman\Transport\Connection;
use SD\Gearman\Transport\GearmanTransport;
use SD\Gearman\Transport\GearmanTransportFactory;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GearmanTransportFactoryTest extends TestCase
{
    public function testSupportsOnlyGearmanTransports()
    {
        $factory = new GearmanTransportFactory();

        $this->assertTrue($factory->supports('gearman://', []));
        $this->assertFalse($factory->supports('amqp://', []));
        $this->assertFalse($factory->supports('invalid-dsn', []));
    }

    public function testCreateTransport()
    {
        $factory = new GearmanTransportFactory();
        $dsn = 'gearman://';

        $serializer = $this->createMock(SerializerInterface::class);
        $expectedTransport = new GearmanTransport(Connection::fromDsn($dsn), $serializer);

        $this->assertEquals($expectedTransport, $factory->createTransport($dsn, [], $serializer));
    }
}
