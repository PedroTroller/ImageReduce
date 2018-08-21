FROM php:7.2-cli

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y jpegoptim optipng pngquant gifsicle wget gnupg git unzip \
    && curl -sL https://deb.nodesource.com/setup_8.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g svgo \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /usr/src/reduce

COPY . /usr/src/reduce

RUN composer install
RUN ln -s /usr/src/reduce/bin/reduce /usr/bin/reduce

WORKDIR /app

ENTRYPOINT ["reduce"]
