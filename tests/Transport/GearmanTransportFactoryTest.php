<?php

namespace SD\Tests\Gearman\Transport;

use PHPUnit\Framework\TestCase;
use SD\Gearman\Transport\Connection;
use SD\Gearman\Transport\GearmanTransport;
use SD\Gearman\Transport\GearmanTransportFactory;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @requires extension gearman >= 2.0
 */
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

        // transport_name is added by Messenger by default
        $this->assertEquals($expectedTransport, $factory->createTransport($dsn, ['transport_name' => 'gearman'], $serializer));
    }

    public function testCreateTransportWithOptions()
    {
        $factory = new GearmanTransportFactory();
        $dsn = 'gearman://';
        $options = [
            'hosts' => ['gearman1:4730', 'gearman2:4730'],
        ];

        $serializer = $this->createMock(SerializerInterface::class);
        $expectedTransport = new GearmanTransport(new Connection(array_merge(['timeout' => 100, 'job_names' => ['default']], $options)), $serializer);

        $this->assertEquals($expectedTransport, $factory->createTransport($dsn, $options, $serializer));
    }
}
