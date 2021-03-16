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

    public function testEmptyMessage()
    {
        $connection = Connection::fromDsn('gearman://gearmand');

        $this->assertNull($connection->get());
    }

    public function testAddGet()
    {
        $connection = Connection::fromDsn('gearman://gearmand');

        $connection->send('default', 'junk', []);

        $this->assertEquals(['headers' => [], 'body' => 'junk'], $connection->get());
    }

    public function testNoSuccessfulServers()
    {
        $this->expectException(\RuntimeException::class);

        $connection = Connection::fromDsn('gearman://');
        $connection->get();
    }

    public function testOneServerConnects()
    {
        $connection = new Connection(['hosts' => ['gearmand:4730', 'localhost:4730'], 'timeout' => 100, 'job_names' => ['default']]);

        $this->assertNull($connection->get());
    }
}
