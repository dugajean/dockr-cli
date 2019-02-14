FROM nginx:latest
WORKDIR "/app"

ARG PROJECT_NAME
ARG PROJECT_DOMAIN
ARG PHP_VERSION
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get -y --no-install-recommends install ca-certificates apt-transport-https wget gnupg

# Add PHP apt-repo
RUN wget -q https://packages.sury.org/php/apt.gpg -O- | apt-key add -
RUN echo "deb https://packages.sury.org/php/ stretch main" | tee /etc/apt/sources.list.d/php.list

# Install OpenSSL and PHP
RUN apt-get update && apt-get -y --no-install-recommends install openssl php${PHP_VERSION} php${PHP_VERSION}-cli

# Generate SSL certificate
RUN mkdir /etc/nginx/ssl
RUN openssl req -x509 -nodes -days 5000 -newkey rsa:2048 -keyout /etc/nginx/ssl/nginx.key -out /etc/nginx/ssl/nginx.crt \
    -subj "/C=EU/ST=Germany/L=Berlin/O=${PROJECT_NAME}, Inc./OU=Agency/CN=${PROJECT_DOMAIN}/emailAddress=webmaster@${PROJECT_DOMAIN}"

# Restart nginx
RUN service nginx restart
