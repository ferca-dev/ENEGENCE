DOCKER_COMPOSE := docker compose

.PHONY: init up down artisan composer npm test pint build scan qa logs

init:
	@test -f .env || cp .env.example .env
	$(DOCKER_COMPOSE) build app
	$(DOCKER_COMPOSE) run --rm --no-deps app composer install --no-interaction
	$(DOCKER_COMPOSE) run --rm --no-deps app php artisan key:generate --force
	$(DOCKER_COMPOSE) run --rm --no-deps node npm install --no-audit --no-fund
	$(DOCKER_COMPOSE) up -d db
	$(DOCKER_COMPOSE) run --rm app php artisan migrate --force
	$(DOCKER_COMPOSE) run --rm --no-deps node npm run build
	$(DOCKER_COMPOSE) up -d app

up:
	$(DOCKER_COMPOSE) up -d app

down:
	$(DOCKER_COMPOSE) down

artisan:
	$(DOCKER_COMPOSE) run --rm app php artisan $(cmd)

composer:
	$(DOCKER_COMPOSE) run --rm --no-deps app composer $(cmd)

npm:
	$(DOCKER_COMPOSE) run --rm --no-deps node npm $(cmd)

test:
	$(DOCKER_COMPOSE) run --rm app php artisan test

pint:
	$(DOCKER_COMPOSE) run --rm --no-deps app vendor/bin/pint --test

build:
	$(DOCKER_COMPOSE) run --rm --no-deps node npm run build

scan:
	$(DOCKER_COMPOSE) run --rm --no-deps app sh -lc '! grep -R -n -E "(^|[^[:alnum:]_])(dd|dump|var_dump)[[:space:]]*\\(|TODO|FIXME|AKIA[0-9A-Z]{16}|BEGIN (RSA |OPENSSH |EC |DSA )?PRIVATE KEY" app bootstrap config database resources routes tests Dockerfile compose.yaml README.md .env.example composer.json package.json'

qa: test pint build scan

logs:
	$(DOCKER_COMPOSE) logs --tail=100 app db
