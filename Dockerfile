FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    ca-certificates \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
