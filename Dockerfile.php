FROM php:8.2-fpm

# Instalar dependências
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www

# Copiar arquivos do projeto
COPY . /var/www

# Definir permissões
RUN mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/logs \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copiar arquivo .env de exemplo e gerar chave
RUN cp .env.example .env \
    && sed -i 's/APP_ENV=local/APP_ENV=production/g' .env \
    && sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env \
    && sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/g' .env \
    && sed -i 's/SESSION_DRIVER=database/SESSION_DRIVER=file/g' .env \
    && sed -i 's/CACHE_DRIVER=database/CACHE_DRIVER=file/g' .env \
    && sed -i "s|APP_URL=http://localhost|APP_URL=http://193.203.183.137:8888|g" .env

# Instalar dependências do Composer
RUN composer install --no-interaction --optimize-autoloader

# Gerar chave de aplicação
RUN php artisan key:generate --force

# Instalar dependências do NPM e compilar assets
RUN npm install
RUN npm run build || echo "Build de assets falhou, mas continuando..."

# Adicionar script de inicialização
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expor porta 9000 e iniciar php-fpm
EXPOSE 9000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]