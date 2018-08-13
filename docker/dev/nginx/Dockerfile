FROM ubuntu:16.04
MAINTAINER Gudfar <maesh.kyryll@gmail.com>

RUN rm -fr /var/lib/apt/lists

RUN apt-get update && apt-get install -y software-properties-common python-software-properties
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php && add-apt-repository ppa:nginx/stable && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C
RUN apt-get update && apt-get install -y nginx curl git vim unzip supervisor \
php7.1 php7.1-fpm php7.1-cli php7.1-mysql php7.1-gd php7.1-curl php7.1-dev php7.1-mcrypt \
php7.1-xdebug php7.1-intl php7.1-memcached php7.1-xml php7.1-mongodb php7.1-mbstring php7.1-zip php7.1-bcmath



#php
RUN sed -i -e "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g" /etc/php/7.1/fpm/php.ini
RUN sed -i -e "s/upload_max_filesize\s*=\s*2M/upload_max_filesize = 50M/g" /etc/php/7.1/fpm/php.ini
RUN sed -i -e "s/post_max_size\s*=\s*8M/post_max_size = 50M/g" /etc/php/7.1/fpm/php.ini
RUN sed -i "s/error_reporting = .*$/error_reporting = E_ALL/" /etc/php/7.1/fpm/php.ini
RUN sed -i -r 's/display_errors = Off$/display_errors = On/g' /etc/php/7.1/fpm/php.ini

#Enable XDebug
RUN echo "zend_extension = xdebug.so" >> /etc/php/7.1/fpm/php.ini

RUN echo "xdebug.remote_enable=on" >> /etc/php/7.1/fpm/php.ini
RUN echo "xdebug.remote_port=9000" >> /etc/php/7.1/fpm/php.ini
RUN echo "xdebug.remote_connect_back=on" >> /etc/php/7.1/fpm/php.ini
RUN echo "xdebug.remote_handler=dbgp" >> /etc/php/7.1/fpm/php.ini
RUN echo "xdebug.idekey=PHPSTORM" >> /etc/php/7.1/fpm/php.ini
RUN echo "xdebug.profiler_enable=0" >> /etc/php/7.1/fpm/php.ini
RUN echo "xdebug.profiler_output_dir='/var/log'" >> /etc/php/7.1/fpm/php.ini

#nginx
RUN echo "daemon off;" >> /etc/nginx/nginx.conf

COPY ./site.conf /etc/nginx/sites-available/default


# Define working directory.
WORKDIR /www

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

EXPOSE 80

CMD service php7.1-fpm start && \
    nginx
