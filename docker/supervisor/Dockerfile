FROM ubuntu:focal

ENV TZ Europe/Berlin

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt update && \
    apt install -y \
    software-properties-common

RUN add-apt-repository ppa:chris-needham/ppa
RUN apt update && \
    apt install -y \
    git \
    supervisor \
    apache2 \
    php7.4 \
    php7.4-bcmath \
    php7.4-cli \
    php7.4-curl \
    php7.4-dev \
    php7.4-gd \
    php7.4-iconv \
    php7.4-imap \
    php7.4-intl \
    php7.4-json \
    php7.4-mbstring \
    php7.4-mysql \
    php7.4-opcache \
    php7.4-soap \
    php7.4-xml \
    php7.4-zip \
    php7.4-amqp \
    php-imagick \
    php-xdebug \
    audiowaveform

RUN a2enmod ssl

RUN php -r "copy('https://getcomposer.org/composer.phar', '/usr/local/bin/composer');" \
    && chmod ugo+x /usr/local/bin/composer \
    && composer self-update

RUN curl -sL https://deb.nodesource.com/setup_12.x | bash - \
    && apt-get install -y nodejs

RUN curl -o- -L https://yarnpkg.com/install.sh | bash

COPY ./etc/supervisor/conf.d/ /etc/supervisor/conf.d

EXPOSE 9001

WORKDIR /var/www/html

CMD ["supervisord", "-n"]