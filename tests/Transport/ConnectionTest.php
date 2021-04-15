<?php

namespace SD\Tests\Gearman\Transport;

use PHPUnit\Framework\TestCase;
use SD\Gearman\Transport\Connection;

/**
 * @requires extension gearman >= 2.0
 * @group integration
 */
class ConnectionTest extends TestCase
{
    public function testSimpleDsn()
    {
        $dsn = 'gearman://';
        $expectedConnection = new Connection(['hosts' => ['localhost:4730'], 'timeout' => 100, 'job_names' => ['default']]);

        $this->assertEquals($expectedConnection, Connection::fromDsn($dsn));
    }

    public function testDsnHostAndPortOverride()
    {
        $dsn = 'gearman://gearmand:7003';
        $expectedConnection = new Connection(['hosts' => ['gearmand:7003'], 'timeout' => 100, 'job_names' => ['default']]);

        $this->assertEquals($expectedConnection, Connection::fromDsn($dsn));
    }

    public function testMultipleJobs()
    {
        $dsn = 'gearman://localhost/?job_names[]=foo&job_names[]=bar';
        $expectedConnection = new Connection(['hosts' => ['localhost:4730'], 'timeout' => 100, 'job_names' => ['foo', 'bar']]);

        $this->assertEquals($expectedConnection, Connection::fromDsn($dsn));
    }

    public function testInvalidOptions()
    {
        $dsn = 'gearman://localhost?what=is&this';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option(s) "what", "this" passed to Gearman Messenger transport.');

        Connection::fromDsn($dsn);
    }

    /**
     * @group integration
     */
    public function testEmptyMessage()
    {
        $connection = Connection::fromDsn('gearman://');

        $this->assertNull($connection->get());
    }

    /**
     * @group integration
     */
    public function testAddGet()
    {
        $connection = Connection::fromDsn('gearman://');

        $connection->send('default', 'junk', []);

        $this->assertEquals(['headers' => [], 'body' => 'junk'], $connection->get());
    }

    /**
     * @group integration
     */
    public function testNoSuccessfulServers()
    {
        $this->expectException(\RuntimeException::class);

        $connection = Connection::fromDsn('gearman://');
        $connection->get();
    }

    /**
     * @group integration
     */
    public function testOneServerConnects()
    {
        // 4731 port doesn't exist
        $connection = new Connection(['hosts' => ['localhost:4731', 'localhost:4730'], 'timeout' => 100, 'job_names' => ['default']]);

        $this->assertNull($connection->get());
    }

    public function testHostsInOptions()
    {
        $connection = Connection::fromDsn('gearman://', ['hosts' => ['gearman1:4730', 'gearman1:4730']]);
        $expectedConnection = new Connection(['hosts' => ['gearman1:4730', 'gearman1:4730'], 'timeout' => 100, 'job_names' => ['default']]);

        $this->assertEquals($expectedConnection, $connection);
    }
}
