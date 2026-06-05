# =============================================================
# Makefile — Shortcuts para Docker + Laravel
# =============================================================

.PHONY: up down build rebuild shell artisan migrate fresh logs ps help

## Levantar todos los servicios
up:
	docker compose up -d

## Detener todos los servicios
down:
	docker compose down

## Construir imágenes (primera vez o tras cambios)
build:
	docker compose build --no-cache

## Reconstruir e iniciar
rebuild: build up

## Abrir shell dentro del contenedor app
shell:
	docker compose exec app sh

## Ejecutar un comando artisan: make artisan cmd="migrate --seed"
artisan:
	docker compose exec app php artisan $(cmd)

## Correr migraciones
migrate:
	docker compose exec app php artisan migrate

## Resetear DB y re-seedear
fresh:
	docker compose exec app php artisan migrate:fresh --seed

## Ver logs en tiempo real
logs:
	docker compose logs -f

## Estado de los contenedores
ps:
	docker compose ps

## Ayuda
help:
	@echo ""
	@echo "  make up       — Iniciar servicios"
	@echo "  make down     — Detener servicios"
	@echo "  make build    — Construir imagen desde cero"
	@echo "  make rebuild  — Reconstruir e iniciar"
	@echo "  make shell    — Shell dentro del contenedor app"
	@echo "  make artisan cmd='...' — Ejecutar artisan"
	@echo "  make migrate  — Correr migraciones"
	@echo "  make fresh    — Resetear BD y sembrar"
	@echo "  make logs     — Ver logs en vivo"
	@echo "  make ps       — Estado de contenedores"
	@echo ""
