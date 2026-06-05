FROM php:8.2-cli

WORKDIR /app

RUN apt-get update \
  && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
  && docker-php-ext-install curl mysqli pdo_mysql \
  && rm -rf /var/lib/apt/lists/*

COPY . .

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"]
