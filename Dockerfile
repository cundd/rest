##
# Docker file for cundd/rest testing
#
# Build:
# > docker build -t cundd/rest .
#
# Build for TYPO3 7.6:
# > docker build -t cundd/rest --build-arg TYPO3=TYPO3_7-6 .
#
# Run tests:
# > docker-compose run test
#
FROM php:7.1-cli

# -----------------------------------------------------------------
# PREPARE THE OS

RUN apt-get update && apt-get install -y git zip mysql-client
RUN docker-php-ext-install opcache mysqli
#RUN docker-php-ext-install iconv mcrypt zip opcache mysqli pdo_mysql gd

# -----------------------------------------------------------------
# INSTALL COMPOSER

COPY ./Resources/Private/Scripts/composer-install.sh /app/Resources/Private/Scripts/composer-install.sh
RUN bash /app/Resources/Private/Scripts/composer-install.sh


# -----------------------------------------------------------------
# INSTALL TYPO3

# Install TYPO3 master branch
ARG TYPO3=master
# MariaDB is linked as host "db" (see docker-composer.yml)
ARG typo3DatabaseHost=db

# Export arguments to ENV
ENV TYPO3=${TYPO3}
ENV typo3DatabaseHost=${typo3DatabaseHost}

COPY ./Build /app/Build
RUN bash /app/Build/install.sh install_typo3 && bash /app/Build/install.sh prepare_database


VOLUME /app
WORKDIR /app

# Defaults for which tests to run
ENV FUNCTIONAL_TESTS=yes
ENV UNIT_TESTS=yes
ENV DOCUMENTATION_TESTS=yes
ENV MANUAL_TESTS=no

ENTRYPOINT [ "bash", "/app/Build/test.sh" ]

