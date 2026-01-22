# Script de inicialización del proyecto para Windows PowerShell
# Uso: .\scripts\init.ps1

$ErrorActionPreference = "Stop"

Write-Host "🚀 Iniciando configuración del proyecto..." -ForegroundColor Cyan

# Verificar que Docker está corriendo
try {
    docker info | Out-Null
} catch {
    Write-Host "Error: Docker no está corriendo. Por favor, inicia Docker Desktop." -ForegroundColor Red
    exit 1
}

# Copiar archivo de entorno si no existe
if (-not (Test-Path ".env")) {
    Write-Host "Creando archivo .env..." -ForegroundColor Yellow
    Copy-Item "env.example" ".env"
}

# Construir y levantar contenedores
Write-Host "Construyendo contenedores Docker..." -ForegroundColor Yellow
docker-compose up -d --build

# Esperar a que PostgreSQL esté listo
Write-Host "Esperando a que PostgreSQL esté listo..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Instalar dependencias
Write-Host "Instalando dependencias de Composer..." -ForegroundColor Yellow
docker-compose exec -T php composer install --no-interaction

# Generar claves JWT
Write-Host "Generando claves JWT..." -ForegroundColor Yellow
docker-compose exec -T php mkdir -p config/jwt
docker-compose exec -T php openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:tickets_jwt_passphrase
docker-compose exec -T php openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:tickets_jwt_passphrase

# Ejecutar migraciones
Write-Host "Ejecutando migraciones de base de datos..." -ForegroundColor Yellow
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

# Limpiar caché
Write-Host "Limpiando caché..." -ForegroundColor Yellow
docker-compose exec -T php php bin/console cache:clear

Write-Host ""
Write-Host "¡Proyecto configurado exitosamente!" -ForegroundColor Green
Write-Host ""
Write-Host "La API está disponible en: http://localhost:8080" -ForegroundColor Cyan
Write-Host ""
Write-Host "Comandos útiles:" -ForegroundColor Cyan
Write-Host "   docker-compose exec php bash     # Entrar al contenedor"
Write-Host "   docker-compose logs -f           # Ver logs"
Write-Host "   docker-compose down              # Detener contenedores"
Write-Host ""
