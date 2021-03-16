<?php

namespace SD\Gearman\Transport;

use GearmanClient;
use GearmanJob;
use GearmanWorker;

/**
 * A Gearman Job Server connection.
 *
 * @author Brian Feaver <brian.feaver@slickdeals.net>
 *
 * @final
 */
class Connection
{
    private const AVAILABLE_OPTIONS = [
        'hosts',
        'job_names',
        'timeout',
    ];

    private $options;
    private $messageBuffer;
    private $worker = null;
    private $client = null;

    public function __construct(array $options)
    {
        if (!\extension_loaded('gearman')) {
            throw new \LogicException(sprintf('You cannot use the "%s" as the "gearman" extension is not installed.', __CLASS__));
        }
        $this->options = $options;
    }

    /**
     * Creates a connection based on the DSN and options.
     *
     * Available options:
     *   * hosts: Array of host:port strings
     *   * job_names: Array of gearman job names to consume
     *   * timeout: The amount of time in milliseconds before timing out
     */
    public static function fromDsn(string $dsn): self
    {
        if (false === $parsedUrl = parse_url($dsn)) {
            if (!\in_array($dsn, ['gearman://'])) {
                throw new \InvalidArgumentException(sprintf('The given Gearman DSN "%s" is invalid.', $dsn));
            }

            $parsedUrl = [];
        }

        parse_str($parsedUrl['query'] ?? '', $parsedQuery);

        $host = $parsedUrl['host'] ?? 'localhost';
        $port = $parsedUrl['port'] ?? 4730;

        $gearmanOptions = array_replace_recursive([
            'hosts' => ["$host:$port"],
            'timeout' => 100,
            'job_names' => [
                'default',
            ],
        ], $parsedQuery);

        self::validateOptions($gearmanOptions);

        return new Connection($gearmanOptions);
    }

    private static function validateOptions(array $options): void
    {
        if (0 < \count($invalidOptions = array_diff(array_keys($options), self::AVAILABLE_OPTIONS))) {
            throw new \InvalidArgumentException(sprintf('Invalid option(s) "%s" passed to Gearman Messenger transport.', implode('", "', $invalidOptions)));
        }

        if (\is_array($options['job_names'] ?? false)) {
            foreach ($options['job_names'] as $job) {
                if (!\is_string($job)) {
                    throw new \InvalidArgumentException(sprintf('Invalid job name "%s" passed to Gearman Messenger transport.', $job));
                }
            }
        }
    }

    public function get(): ?array
    {
        set_error_handler(function ($severity, $message, $file, $line): bool {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $this->messageBuffer = null;
            ($this->worker ?? $this->getWorker())->work();
        } finally {
            restore_error_handler();
        }

        return $this->messageBuffer;
    }

    public function send(string $function, string $body, array $headers): void
    {
        $payload = json_encode([
            'headers' => $headers,
            'body' => $body,
        ]);

        ($this->client ?? $this->getClient())->doBackground($function, $payload);
    }

    private function getClient(): GearmanClient
    {
        $client = new GearmanClient();

        foreach ($this->options['hosts'] as $s) {
            $client->addServers($s);
        }

        return $this->client = $client;
    }

    private function getWorker(): GearmanWorker
    {
        $worker = new GearmanWorker();
        $worker->setTimeout($this->options['timeout']);

        $succeeded = false;
        foreach ($this->options['hosts'] as $s) {
            try {
                $worker->addServers($s);
                $succeeded = true; // At least one succeeded
            } catch (\Throwable $e) {
                if (GEARMAN_GETADDRINFO === $worker->returnCode()) {
                    continue;
                }
                if ('Failed to set exception option' !== $e->getMessage()) {
                    throw $e;
                }
            }
        }

        if (!$succeeded) {
            throw new \RuntimeException('Unable to connect to any Gearman servers.');
        }

        foreach ($this->options['job_names'] as $function) {
            $worker->addFunction($function, function (GearmanJob $job) {
                $data = json_decode($job->workload(), true);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new \InvalidArgumentException(sprintf('Bad json in message. Error "%s", message "%s"', json_last_error(), json_last_error_msg()));
                }
                $this->messageBuffer = $data;
            });
        }

        return $this->worker = $worker;
    }
}
