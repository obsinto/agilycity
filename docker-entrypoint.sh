#!/bin/bash
set -e

echo "Iniciando o container PHP..."

# Se o comando for php-fpm
if [ "$1" = "php-fpm" ]; then
    echo "Esperando o banco de dados iniciar..."
    sleep 10
    
    # Verificar conexão com o banco de dados em background
    (
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
        
        # Agora é seguro limpar caches
        echo "Limpando caches..."
        php artisan config:clear
        # Comentando os comandos problemáticos por enquanto
        # php artisan cache:clear
        # php artisan view:clear
        # php artisan route:clear
        
        echo "Configuração concluída!"
    ) &
    
    # Inicie o PHP-FPM independentemente
    echo "Iniciando PHP-FPM..."
    exec php-fpm
else
    # Para outros comandos, execute-os diretamente
    exec "$@"
fi
EOF

chmod +x docker-entrypoint.sh