#!/bin/bash

# Script de inicialización del proyecto
# Uso: ./scripts/init.sh

set -e

echo "Iniciando configuración del proyecto..."

# Verificar que Docker está corriendo
if ! docker info > /dev/null 2>&1; then
    echo "Error: Docker no está corriendo. Por favor, inicia Docker Desktop."
    exit 1
fi

# Copiar archivo de entorno si no existe
if [ ! -f .env ]; then
    echo "Creando archivo .env..."
    cp env.example .env
fi

# Construir y levantar contenedores
echo "Construyendo contenedores Docker..."
docker-compose up -d --build

# Esperar a que PostgreSQL esté listo
echo "Esperando a que PostgreSQL esté listo..."
sleep 10

# Instalar dependencias
echo "Instalando dependencias de Composer..."
docker-compose exec -T php composer install --no-interaction

# Generar claves JWT
echo "Generando claves JWT..."
docker-compose exec -T php mkdir -p config/jwt
docker-compose exec -T php openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:tickets_jwt_passphrase
docker-compose exec -T php openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:tickets_jwt_passphrase

# Ejecutar migraciones
echo "Ejecutando migraciones de base de datos..."
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

# Limpiar caché
echo "Limpiando caché..."
docker-compose exec -T php php bin/console cache:clear

echo ""
echo " Proyecto configurado exitosamente"
echo ""
echo "  La API está disponible en: http://localhost:8080"
echo ""
echo "  Comandos útiles:"
echo "   docker-compose exec php bash     # Entrar al contenedor"
echo "   docker-compose logs -f           # Ver logs"
echo "   docker-compose down              # Detener contenedores"
echo ""
