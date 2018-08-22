FROM php:7.2-cli

RUN apt-get update \
    && apt-get install -y jpegoptim optipng pngquant gifsicle wget gnupg git unzip \
    && curl -sL https://deb.nodesource.com/setup_8.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g svgo \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /usr/src/reduce

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer global require hirak/prestissimo
COPY composer.json /usr/src/reduce/composer.json
RUN composer install

COPY . /usr/src/reduce
RUN ln -s /usr/src/reduce/bin/reduce /usr/bin/reduce

WORKDIR /app

ENTRYPOINT ["reduce"]
