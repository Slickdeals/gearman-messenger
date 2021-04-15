# Simple image to install gearman extension for local testing
FROM php:7.4-cli

RUN apt-get update; \
    apt-get install -y --no-install-recommends \
        libgearman-dev \
    ; \
    pecl install \
        gearman \
    ; \
    pecl clear-cache; \
    docker-php-ext-enable \
        gearman \
    ; \
    rm -rf /var/lib/apt/lists/*; \
    apt-get clean
