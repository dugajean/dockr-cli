ARG PHP_VERSION_x10
FROM phpdockerio/php${PHP_VERSION_x10}-fpm:latest
WORKDIR "/app"

ARG PHP_VERSION
ARG PHP_EXTENSIONS
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install php${PHP_VERSION}-mysql ${PHP_EXTENSIONS} \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install git
RUN apt-get update \
    && apt-get -y install git \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

EXPOSE 9000
