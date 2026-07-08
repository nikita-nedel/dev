# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console
NPM      = $(PHP_CONT) npm

.PHONY: help build up start down logs sh bash composer vendor sf test npm-install npm-build npm-watch npm-dev migrate migration

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————

build: ## Builds the Docker images
	@$(DOCKER_COMP) build

up: ## Start the docker hub in detached mode (no logs)
	$(DOCKER_COMP) up -d --remove-orphans

start: ## Full project bootstrap: docker, deps, assets, migrations
	@$(MAKE) build
	@$(MAKE) up
	@$(MAKE) composer
	@$(MAKE) npm-install
	@$(MAKE) npm-build
	@$(MAKE) migrate

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the container
	@$(PHP_CONT) sh

bash: ## Connect to the container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

## —— Composer 🧙 ——————————————————————————————————————————————————————————————

composer: ## Install composer dependencies
	@$(COMPOSER) install

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— Npm 🎵 ———————————————————————————————————————————————————————————————
npm-install: ## Install node modules
	@ $(NPM) install

npm-build: ## Run npm build
	@ $(NPM) run build

npm-watch: ## Run dev --watch if doesn't work use export NODE_OPTIONS=--openssl-legacy-provider
	@ $(NPM) run watch

npm-dev: ## Run npm dev
	@ $(NPM) run dev

migrate:
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction

migration:
	@$(SYMFONY) make:migration --no-interaction