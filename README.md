Gearman Messenger
=================

Provides Gearman integration for Symfony Messenger.

[![Build gearman-messenger](https://github.com/Slickdeals/gearman-messenger/actions/workflows/php.yml/badge.svg)](https://github.com/Slickdeals/gearman-messenger/actions/workflows/php.yml)

Installation
------------

```shell
composer require slickdeals/gearman-messenger
```

Usage
-----
You will need to add a service to create the transport factory.

```yaml
# config/services.yaml
services:
    SD\Gearman\Transport\GearmanTransportFactory:
        tags: [ messenger.transport_factory ]
```

To configure the transport, you'll need to provide a DSN with host, port, and job names. You can specify multiple job
names to allow the transport to pull work from multiple Gearman job queues. Routing of messages to handlers in Messenger
will still be based on what workload is decoded.

| Key | Default Value |
|-----|---------------|
| host | localhost |
| port | 4730 |
| job_names[] | default |
| timeout | 100 (milliseconds) |


```yaml
framework:
    messenger:
        transports:
            gearman:
                # DSN will use defaults
                dsn: "gearman://"
                # dsn: 'gearman://<host>:<port>?job_names[]=<job1>&timeout=100'
```

This allows you to have a single transport will support all of your messages, but it does not prevent you from setting up
multiple transports that each handle their own set of messages.

### Specify Multiple Hosts

You can also pass an array of host:port strings as an option to connect to multiple Gearman Job Servers. Keep the DSN
simple as the DSN overrides options.

```yaml
framework:
    messenger:
        transports:
            gearman:
                dsn: "gearman://"
                options:
                    hosts:
                        - gearman1:4730
                        - gearman2:4730
                        # ...
```
