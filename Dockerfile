FROM php:8.2-fpm

# Copy composer.lock and composer.json
COPY ./composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    libpng-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libmcrypt-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libpq-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions

# Clone and build Swoole from source
RUN cd /tmp && \
    git clone https://github.com/swoole/swoole-src.git && \
    cd swoole-src && \
    git checkout v5.1.0 && \
    phpize && \
    ./configure --enable-openssl && \
    make -j$(nproc) && \
    make install && \
    docker-php-ext-enable swoole && \
    cd / && rm -rf /tmp/swoole-src

RUN cd /tmp && \
    git clone https://github.com/phpredis/phpredis.git && \
    cd phpredis && \
    phpize && \
    ./configure && \
    make -j$(nproc) && \
    make install && \
    docker-php-ext-enable redis && \
    cd / && rm -rf /tmp/phpredis


#RUN pecl install -D 'enable-openssl="yes"' swoole && pecl install redis
#RUN docker-php-ext-enable swoole && docker-php-ext-enable redis
RUN docker-php-ext-configure gd --with-webp --with-jpeg
RUN docker-php-ext-install gd pgsql pdo_pgsql mbstring zip exif pcntl bcmath soap curl sockets


# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
#COPY . /var/www


# set application directory permissions
RUN chown www:www  /var/www


# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
