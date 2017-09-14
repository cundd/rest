##
# Docker file for cundd/rest testing
#
# Build:
# > docker build -t cundd/rest .
#
# Build for TYPO3 7.6:
# > docker build -t cundd/rest --build-arg TYPO3=TYPO3_7-6 .
#
FROM php:7.1-cli

# -----------------------------------------------------------------
# PREPARE THE OS

RUN apt-get update && apt-get install -y git zip
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

COPY ./Build /app/Build
RUN bash /app/Build/install.sh install_typo3 && bash /app/Build/install.sh prepare_database


VOLUME /app
WORKDIR /app


ENTRYPOINT [ "bash", "/app/Build/test.sh" ]
#CMD [ "php", "./bin/console", "server:start", "0.0.0.0" ]
