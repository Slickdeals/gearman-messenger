Gearman Messenger
=================

Provides Gearman integration for Symfony Messenger.

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
