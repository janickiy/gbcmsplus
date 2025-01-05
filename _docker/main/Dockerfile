#########################
###        BASE       ###
#########################
FROM ubuntu:jammy as base

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    DOCKER_LOG="/tmp/docker.log" \
    WWW_HOME="/var/www" \
    PHP_VERSION="7.1"

WORKDIR $WWW_HOME

RUN apt-get update && \
    apt-get install -y gnupg curl wget git unzip && \
    echo 'deb http://archive.ubuntu.com/ubuntu/ jammy main restricted universe multiverse' > /etc/apt/sources.list && \
    echo 'deb http://archive.ubuntu.com/ubuntu/ jammy-updates main restricted universe multiverse' >> /etc/apt/sources.list && \
    echo 'deb http://archive.ubuntu.com/ubuntu/ jammy-backports main restricted universe multiverse' >> /etc/apt/sources.list && \
    echo 'deb http://security.ubuntu.com/ubuntu jammy-security main restricted universe multiverse' >> /etc/apt/sources.list && \
    echo 'deb http://ppa.launchpad.net/ondrej/nginx-mainline/ubuntu jammy main' >> /etc/apt/sources.list && \
    echo 'deb http://ppa.launchpad.net/ondrej/php/ubuntu jammy main' >> /etc/apt/sources.list && \
    curl -fs https://nginx.org/keys/nginx_signing.key | apt-key add - > /dev/null 2>&1 && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys DE1997DCDE742AFA && \
    apt-get update && \
    apt-get upgrade -y && \
    apt-get dist-upgrade -y && \
    apt-get purge -y gnupg  && \
    apt-get autoremove --purge -y && \
    apt-get install -y nano \
                       supervisor \
                       nginx \
                       ruby-sass \
                       mysql-client \
                       libfcgi0ldbl \
                       php${PHP_VERSION}-common \
                       php${PHP_VERSION}-fpm \
                       php${PHP_VERSION}-cli \
                       php${PHP_VERSION}-xml \
                       php${PHP_VERSION}-json \
                       php${PHP_VERSION}-soap \
                       php${PHP_VERSION}-curl \
                       php${PHP_VERSION}-mysqli \
                       php${PHP_VERSION}-sqlite \
                       php${PHP_VERSION}-mbstring \
                       php${PHP_VERSION}-mcrypt \
                       php${PHP_VERSION}-bcmath \
                       php${PHP_VERSION}-zip \
                       php${PHP_VERSION}-bz2 \
                       php${PHP_VERSION}-gd \
                       php${PHP_VERSION}-opcache \
                       php${PHP_VERSION}-imagick \
                       php${PHP_VERSION}-memcache \
                       php${PHP_VERSION}-memcached \
                       php${PHP_VERSION}-imap \
                       php${PHP_VERSION}-intl \
                       php${PHP_VERSION}-redis \
                       php${PHP_VERSION}-xdebug \
                       php${PHP_VERSION}-sockets \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mv /etc/php/${PHP_VERSION} /etc/php/current && ln -s /etc/php/current /etc/php/${PHP_VERSION} && \
    rm -rf /etc/php/current/cli/conf.d && ln -s /etc/php/current/fpm/conf.d /etc/php/current/cli/conf.d && \
    rm -f /etc/php/current/cli/php.ini && ln -s /etc/php/current/fpm/php.ini /etc/php/current/cli/php.ini && \
    ln -s /usr/sbin/php-fpm${PHP_VERSION} /usr/sbin/php-fpm && \
    rm -rf /etc/php/latest/fpm/pool.d/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer self-update --1 # && composer global require "hirak/prestissimo"

RUN rm -rf /etc/nginx/modules-enabled/* -rf && \
    rm -rf /etc/nginx/sites-available/* -rf && \
    rm -rf /etc/nginx/sites-enabled/* -rf && \
    rm -f /etc/nginx/fastcgi_params -rf && \
    rm -f /var/www/html -rf && \
    rm -f /etc/nginx/nginx.conf && \
    unlink /var/log/nginx/error.log && \
    unlink /var/log/nginx/access.log && \
    mkdir $WWW_HOME -p


COPY ./nginx/conf.d /etc/nginx/conf.d
COPY ./nginx/fastcgi.conf /etc/nginx/fastcgi.conf
COPY ./nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./php-fpm/fpm /etc/php/current/fpm
COPY ./php-fpm/php.ini /etc/php/current/fpm/conf.d/99-custom.ini

COPY ./entrypoint.sh /entrypoint.sh
COPY ./supervisor /etc/supervisor
COPY ./health.sh /health.sh

RUN mkfifo --mode 0666 $DOCKER_LOG

ENV PATH "$PATH:$WWW_HOME/vendor/codeception/codeception"

EXPOSE 80

#ENTRYPOINT ["tail", "-f", "/dev/null"]
ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]

HEALTHCHECK --timeout=10s CMD /bin/bash /health.sh
