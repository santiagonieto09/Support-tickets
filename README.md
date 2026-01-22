# Sistema de Tickets de Soporte - API REST

## Descripcion del Proyecto

Este proyecto es una **API REST** para la gestion de tickets de soporte, desarrollada como demostracion de competencias en el desarrollo de software moderno con **PHP** y el framework **Symfony**.

El objetivo principal es presentar un acercamiento practico a:

- **Dockerizacion de aplicaciones**: Configuracion de contenedores para PHP, PostgreSQL y Nginx, permitiendo entornos de desarrollo completamente replicables e independientes del sistema operativo del desarrollador.

- **Integracion y Despliegue Continuo (CI/CD)**: Implementacion de pipelines automatizados con GitHub Actions que ejecutan pruebas, analisis de seguridad y construccion de imagenes Docker, sentando las bases para despliegues automaticos en la nube (AWS).

- **Arquitectura en Capas (Clean Architecture)**: Separacion clara de responsabilidades entre las capas de Presentacion, Aplicacion, Dominio e Infraestructura, facilitando el mantenimiento, testing y escalabilidad del codigo.

- **Buenas Practicas de Desarrollo**: Aplicacion de principios SOLID, patrones de diseno (Repository, DTO, Dependency Injection), Clean Code y desarrollo seguro.

- **Pruebas Unitarias**: Suite de tests con PHPUnit que validan la logica de negocio, entidades y servicios, garantizando la calidad del codigo.

- **Configuracion del Framework Symfony**: Manejo de archivos de configuracion YAML para servicios, seguridad, Doctrine ORM, JWT y rutas, demostrando conocimiento profundo del ecosistema Symfony.

Este proyecto representa una base solida que puede ser extendida y adaptada a requerimientos empresariales reales, demostrando la capacidad de construir APIs robustas, seguras y mantenibles.

---

## Arquitectura del Proyecto

El proyecto sigue una **arquitectura en capas** (Clean Architecture) con los siguientes componentes:

```
src/
├── Application/        # Casos de uso, DTOs, Services
│   ├── DTO/           # Data Transfer Objects (validacion de entrada)
│   └── Service/       # Logica de negocio y orquestacion
├── Domain/            # Nucleo del negocio (sin dependencias externas)
│   ├── Entity/        # Entidades de dominio (User, Ticket)
│   ├── Exception/     # Excepciones de dominio personalizadas
│   ├── Repository/    # Interfaces de repositorios (contratos)
│   └── ValueObject/   # Enums y Value Objects (Status, Priority)
├── Infrastructure/    # Implementaciones concretas
│   └── Repository/    # Implementaciones Doctrine de los repositorios
└── Presentation/      # Capa de entrada/salida HTTP
    └── Controller/    # Controladores REST de la API
```

### Flujo de Datos

```
Request HTTP → Controller → Service → Repository Interface → Doctrine Repository → Database
                   ↓
              Validacion DTO
                   ↓
              Logica de Negocio
                   ↓
              Response JSON
```

---

## Stack Tecnologico

| Componente | Tecnologia | Proposito |
|------------|------------|-----------|
| Lenguaje | PHP 8.3 | Lenguaje principal con tipado estricto |
| Framework | Symfony 7.2 | Framework robusto para aplicaciones empresariales |
| Base de datos | PostgreSQL 16 | Base de datos relacional de alto rendimiento |
| ORM | Doctrine | Mapeo objeto-relacional y migraciones |
| Autenticacion | JWT (lexik/jwt-authentication-bundle) | Tokens stateless para APIs |
| Testing | PHPUnit 10 | Framework de pruebas unitarias |
| Contenedores | Docker + Docker Compose | Entornos replicables y aislados |
| CI/CD | GitHub Actions | Automatizacion de pruebas y despliegue |
| Servidor Web | Nginx | Proxy reverso de alto rendimiento |

---

## Estructura de Archivos de Configuracion

```
config/
├── bundles.php                    # Registro de bundles de Symfony
├── packages/
│   ├── doctrine.yaml             # Configuracion de Doctrine ORM
│   ├── doctrine_migrations.yaml  # Configuracion de migraciones
│   ├── framework.yaml            # Configuracion del framework
│   ├── lexik_jwt_authentication.yaml  # Configuracion JWT
│   ├── nelmio_cors.yaml          # Configuracion CORS para API
│   └── security.yaml             # Firewalls, providers y access control
├── routes/
│   └── security.yaml             # Ruta de login
├── routes.yaml                   # Configuracion de rutas
└── services.yaml                 # Inyeccion de dependencias
```

---

## Instalacion con Docker

### Prerrequisitos
- Docker Desktop instalado
- Git

### Pasos de Instalacion

1. **Clonar el repositorio:**
```bash
git clone <repository-url>
cd Support-tickets
```

2. **Construir y levantar contenedores:**
```bash
docker-compose up -d --build
```

3. **Instalar dependencias:**
```bash
docker-compose exec php composer install
```

4. **Generar claves JWT:**
```bash
docker-compose exec php php bin/console lexik:jwt:generate-keypair
```

5. **Ejecutar migraciones:**
```bash
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

6. **La API estara disponible en:** `http://localhost:8080`

---

## API Endpoints

### Autenticacion (publicos)

| Metodo | Endpoint | Descripcion |
|--------|----------|-------------|
| POST | `/api/register` | Registro de nuevo usuario |
| POST | `/api/login` | Autenticacion (retorna JWT) |

### Tickets (requieren autenticacion JWT)

| Metodo | Endpoint | Descripcion |
|--------|----------|-------------|
| GET | `/api/tickets` | Listar tickets del usuario autenticado |
| POST | `/api/tickets` | Crear nuevo ticket |
| GET | `/api/tickets/{id}` | Obtener ticket especifico |
| PUT | `/api/tickets/{id}` | Actualizar ticket |
| DELETE | `/api/tickets/{id}` | Eliminar ticket |

### Ejemplos de Uso

**Registro de usuario:**
```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@ejemplo.com",
    "password": "password123",
    "name": "Juan Perez"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@ejemplo.com",
    "password": "password123"
  }'
```

**Crear ticket (con JWT):**
```bash
curl -X POST http://localhost:8080/api/tickets \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <JWT_TOKEN>" \
  -d '{
    "title": "Error en el sistema",
    "description": "El sistema muestra un error 500 al intentar guardar",
    "priority": "high"
  }'
```

---

## Modelo de Datos

### Entidad User
| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | UUID v7 | Identificador unico |
| email | string | Email (unico) |
| password | string | Contrasena hasheada (bcrypt/argon2) |
| name | string | Nombre del usuario |
| roles | json | Roles del usuario |
| createdAt | datetime | Fecha de creacion |

### Entidad Ticket
| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | UUID v7 | Identificador unico |
| title | string | Titulo del ticket (5-255 caracteres) |
| description | text | Descripcion detallada (min 10 caracteres) |
| status | enum | Estado: open, in_progress, resolved, closed |
| priority | enum | Prioridad: low, medium, high, critical |
| userId | UUID | Usuario propietario (FK) |
| createdAt | datetime | Fecha de creacion |
| updatedAt | datetime | Ultima actualizacion |

### Maquina de Estados del Ticket
```
open ──────────────→ in_progress ──────────────→ resolved
  │                      │                          │
  │                      ↓                          │
  │                    open                         │
  │                      │                          │
  ↓                      ↓                          ↓
closed ←─────────────────────────────────────────────
```

---

## Pruebas Unitarias

El proyecto incluye una suite completa de tests unitarios que cubren:

- **Value Objects**: Validacion de estados y prioridades
- **Entidades**: Logica de negocio de User y Ticket
- **DTOs**: Transformacion y validacion de datos
- **Servicios**: Casos de uso y reglas de negocio

### Ejecutar Tests

```bash
# Ejecutar todos los tests
docker-compose exec php vendor/bin/phpunit

# Ejecutar con formato detallado
docker-compose exec php vendor/bin/phpunit --testdox

# Ejecutar solo tests unitarios
docker-compose exec php vendor/bin/phpunit --testsuite Unit
```

### Cobertura de Tests
- 65 tests
- 111 assertions
- Cobertura de entidades, servicios, DTOs y value objects

---

## Principios SOLID Aplicados

### S - Single Responsibility Principle
Cada clase tiene una unica responsabilidad:
- **Controllers**: Solo manejan peticiones HTTP y respuestas
- **Services**: Contienen la logica de negocio
- **Repositories**: Gestionan la persistencia de datos
- **DTOs**: Transportan y validan datos entre capas

### O - Open/Closed Principle
El uso de interfaces permite extender funcionalidad sin modificar codigo existente:
- `TicketRepositoryInterface` puede tener multiples implementaciones
- Nuevos estados o prioridades se agregan en los enums sin modificar logica

### L - Liskov Substitution Principle
Las implementaciones de repositorios son intercambiables:
- `DoctrineTicketRepository` puede reemplazarse por cualquier implementacion que cumpla la interfaz

### I - Interface Segregation Principle
Interfaces pequenas y especificas:
- `TicketRepositoryInterface` solo define operaciones de tickets
- `UserRepositoryInterface` solo define operaciones de usuarios

### D - Dependency Inversion Principle
Las capas superiores dependen de abstracciones:
- `TicketService` depende de `TicketRepositoryInterface`, no de `DoctrineTicketRepository`
- La inyeccion de dependencias de Symfony resuelve las implementaciones concretas

---

## Patrones de Diseno Implementados

| Patron | Implementacion | Beneficio |
|--------|----------------|-----------|
| **Repository** | `TicketRepositoryInterface` / `DoctrineTicketRepository` | Abstrae la capa de persistencia |
| **DTO** | `CreateTicketDTO`, `UpdateTicketDTO` | Valida y transporta datos entre capas |
| **Dependency Injection** | Container de Symfony | Desacopla componentes y facilita testing |
| **Value Object** | `TicketStatus`, `TicketPriority` (enums) | Encapsula logica de valores de dominio |
| **Factory Method** | `DTO::fromArray()` | Crea objetos desde arrays de request |

---

## Seguridad

- **Autenticacion JWT**: Tokens stateless con expiracion configurable
- **Hashing de contrasenas**: bcrypt/argon2 via Symfony PasswordHasher
- **Validacion de entrada**: Constraints de Symfony Validator en DTOs
- **Control de acceso**: Cada usuario solo accede a sus propios tickets
- **Headers de seguridad**: X-Frame-Options, X-Content-Type-Options, X-XSS-Protection en Nginx
- **CORS configurado**: Control de origenes permitidos para la API

---

## CI/CD con GitHub Actions

El proyecto incluye un pipeline automatizado que ejecuta:

1. **Tests**: PHPUnit con PostgreSQL en contenedor
2. **Analisis Estatico**: Verificacion de requisitos de plataforma
3. **Auditoria de Seguridad**: `composer audit` para vulnerabilidades
4. **Build Docker**: Construccion y cache de imagen
5. **Deploy a AWS**: Push a ECR y actualizacion de ECS (rama main)

```yaml
# .github/workflows/ci.yml
on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]
```

---

## Comandos Utiles

```bash
# Entrar al contenedor PHP
docker-compose exec php bash

# Ver rutas disponibles
docker-compose exec php php bin/console debug:router

# Crear nueva migracion
docker-compose exec php php bin/console make:migration

# Ejecutar migraciones
docker-compose exec php php bin/console doctrine:migrations:migrate

# Limpiar cache
docker-compose exec php php bin/console cache:clear

# Ver servicios registrados
docker-compose exec php php bin/console debug:container
```

---

## Estructura Docker

```yaml
services:
  php:        # PHP 8.3-FPM con extensiones necesarias
  nginx:      # Servidor web como proxy reverso
  database:   # PostgreSQL 16 Alpine
```

Los contenedores estan configurados para:
- Volumen compartido del codigo fuente
- Red interna para comunicacion entre servicios
- Healthcheck en PostgreSQL para garantizar disponibilidad
- Variables de entorno para configuracion flexible

---

## Licencia

MIT License

---

## Autor

Proyecto desarrollado como demostracion de competencias tecnicas en:
- Desarrollo backend con PHP/Symfony
- Arquitectura de software y patrones de diseño
- Dockerizacion y DevOps
- Pruebas automatizadas
- Buenas practicas de desarrollo (Clean Code, SOLID)
