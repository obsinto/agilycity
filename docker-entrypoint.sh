#!/bin/bash
set -e

echo "Iniciando o container PHP..."

# Se o comando for php-fpm
if [ "$1" = "php-fpm" ]; then
    # Verificar se o composer install foi executado corretamente
    if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
        echo "Diretório vendor não encontrado ou incompleto. Executando composer install..."
        composer install --no-interaction --optimize-autoloader
    fi

    # Verificar a chave da aplicação
    if grep -q "^APP_KEY=$" /var/www/.env || ! grep -q "^APP_KEY=" /var/www/.env; then
        echo "Gerando chave da aplicação..."
        php artisan key:generate --force
    else
        echo "Chave de aplicação já configurada."
    fi

    # Configurações de banco de dados e migrações em background para não bloquear o PHP-FPM
    (
        echo "Esperando o banco de dados iniciar..."
        sleep 10

        # Verificar conexão com o banco de dados
        until php -r "try { new PDO('mysql:host=mysql;dbname=laravel', 'laravel', 'Tz5@Fq8Xw2Dp7&LkN9Js'); echo 'Conectado ao MySQL!'; } catch (Exception \$e) { echo \$e->getMessage(); exit(1); }"; do
            echo "Esperando o MySQL iniciar..."
            sleep 3
        done

        # Criar tabelas de sessão e cache se não existirem
        echo "Executando migrações..."
        php artisan migrate --force --quiet || true
        php artisan session:table --quiet || true
        php artisan cache:table --quiet || true
        php artisan migrate --force --quiet || true

        # Limpar caches
        echo "Limpando caches..."
        php artisan config:clear

        echo "Configuração de background concluída!"
    ) &

    # Definir permissões
    echo "Configurando permissões..."
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

    # Compilar os assets com Vite
    echo "Compilando assets com Vite..."
    npm run build

    echo "Criando link simbólico para storage..."
    php artisan storage:link || true

    # Configurar cron para o agendador do Laravel
    echo "Configurando cron para o agendador Laravel..."
    echo "* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/laravel-scheduler
    chmod 0644 /etc/cron.d/laravel-scheduler
    crontab /etc/cron.d/laravel-scheduler

    # Iniciar o serviço cron
    echo "Iniciando serviço cron..."
    service cron start

    # Inicie o PHP-FPM
    echo "Iniciando PHP-FPM..."
    exec php-fpm
else
    # Para outros comandos, execute-os diretamente
    exec "$@"
fi
