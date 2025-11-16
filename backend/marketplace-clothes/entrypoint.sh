#!/bin/sh

# Ejecutar el scheduler en background
php artisan schedule:work &

# —— Arranca el worker en segundo plano ——
echo "🚀 Iniciando worker..."
php artisan queue:work clothes --queue=clothes_queue --verbose --tries=1 &

# —— Arranca el servidor principal ——
echo "🌐 Iniciando servidor Laravel..."
exec "$@"
