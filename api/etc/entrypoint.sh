#!/bin/bash

echo "⌛ Esperando a que SQL Server esté listo..."

# Esperar hasta que SQL Server acepte conexiones
until /opt/mssql-tools/bin/sqlcmd -S "$DB_HOST,$DB_PORT" -U "$DB_USERNAME" -P "$DB_PASSWORD" -d master -Q "SELECT 1" &>/dev/null; do
    echo "⏳ SQL Server aún no está listo. Reintentando en 20 segundos..."
    sleep 20
done

# Verificar si las dependencias de Composer están instaladas
echo '📦 Verificando dependencias...'
if [ ! "$(ls -A /var/www/html/vendor)" ]; then
  echo '🔄 Instalando dependencias...' && composer install
fi

# Función para ejecutar sqlcmd
function run_sqlcmd() {
    /opt/mssql-tools/bin/sqlcmd -S "$DB_HOST,$DB_PORT" -U "$DB_USERNAME" -P "$DB_PASSWORD" -d master -Q "$1" -h -1
}

# Verificar si la base de datos existe y crearla si no está
echo '🛢️ Verificando base de datos...'
if ! run_sqlcmd "SELECT name FROM sys.databases WHERE name = '$DB_DATABASE';" | grep -q "$DB_DATABASE"; then
    echo '⚠️ Base de datos no encontrada. Creándola...'
    run_sqlcmd "CREATE DATABASE $DB_DATABASE;"
    echo '✅ Base de datos creada exitosamente.'
    DB_CREATED=true
else
    echo '✅ Base de datos encontrada.'
    DB_CREATED=false
fi

# Verificar si la base de datos tiene datos en la tabla 'migrations'
echo '🛢️ Verificando datos en la base de datos...'
MIGRATIONS_COUNT=$(/opt/mssql-tools/bin/sqlcmd -S "$DB_HOST,$DB_PORT" -U "$DB_USERNAME" -P "$DB_PASSWORD" -d "$DB_DATABASE" -Q "SELECT COUNT(*) FROM migrations;" -h -1 | tr -d '[:space:]')

if [ "$DB_CREATED" = true ] || [ "$MIGRATIONS_COUNT" -eq "0" ]; then
    echo '⚠️ La base de datos está vacía o recién creada. Ejecutando migraciones y seeders...'
    php artisan migrate --force
    php artisan db:seed --force
else
    echo '✅ La base de datos ya tiene datos.'
fi

# Iniciar el servidor
echo '🚀 Iniciando servidor...'
php -S 0.0.0.0:8000 -t public
