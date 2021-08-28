FROM php:7.4-cli

ARG INSTALL_XDEBUG
ARG PUID
ARG PGID
ARG USER

RUN apt-get update && apt-get install -y wget git unzip

# XDebug
RUN if [ -n "$INSTALL_XDEBUG" ] && [ "$INSTALL_XDEBUG" = "true" ]; then \
        pecl install xdebug-3.0.4 \
        && docker-php-ext-enable xdebug; \
    fi

# Install Mongo
RUN apt-get install -y libssl-dev libcurl4-openssl-dev pkg-config
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN addgroup --gid ${PGID} ${USER} || true && \
    adduser --uid ${PUID} --gid ${PGID} --gecos '' --disabled-password ${USER} || true