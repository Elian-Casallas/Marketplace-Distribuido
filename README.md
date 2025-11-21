# Marketplace Distribuido

> Repositorio monolítico que contiene el backend (main + nodos) y la carpeta base del frontend.

## Estructura del repositorio
```
/Backend/
   main-api/
   electronics-api/
   clothes-api/
   home-api/
   docker-compose.yml

/Frontend/
   marketplace/

README.md
```

> **Nota**: Cada microservicio (`main-api`, `electronics-api`, `clothes-api`, `home-api`) ya incluye su propio `Dockerfile`, `.env.example` y `.gitignore`. El `docker-compose.yml` en `/Backend` levanta todos los servicios.

---

# Objetivo del proyecto

Marketplace distribuido con nodos por categoría (electronics, clothes, home) y un gateway (main-api) que centraliza rutas y mantiene réplicas en MongoDB. Se implementaron replicación y sincronización con colas/jobs. Pruebas de tolerancia a fallos completada.

# Cómo clonar y preparar backend (rápido)
```
# clonar
git clone <url-del-repo>
cd backend
# revisar archivos .env.example en cada carpeta y personalizar si hace falta
# levantar servicios
docker compose up -d --build
```
> Si usas Docker Compose v1 usa `docker-compose` en lugar de docker compose.

# Comandos útiles (backend)

* Levantar todo: `docker compose up -d --build`

* Ver logs de un servicio: `docker compose logs -f main-api`

* Entrar a bash de un contenedor: `docker exec -it main-api bash`

* Ejecutar artisan: `docker exec -it main-api php artisan <comando>`

* Ejecutar scheduler (si pruebas local): `php artisan schedule:work` dentro del contenedor

* Ejecutar worker: `php artisan queue:work redis --queue=default --tries=3 --timeout=90`


# Cómo clonar y preparar frontend (rápido)
```
# clonar
git clone <url-del-repo>
cd frontend
cd marketplace
# revisar archivos .env.example en cada carpeta y personalizar si hace falta
# levantar el proyecto con el comando de angular
ng serve