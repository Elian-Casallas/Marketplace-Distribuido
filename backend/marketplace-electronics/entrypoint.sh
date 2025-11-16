#!/bin/sh

# —— Arranca el worker en segundo plano ——
echo "🚀 Iniciando worker..."
php artisan queue:work electronics --queue=electronics_queue --verbose --tries=1 &

# —— Arranca el servidor principal ——
echo "🌐 Iniciando servidor Laravel..."
exec "$@"
