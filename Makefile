init: down build up app-install

app-install: app-composer-install

up:
	docker-compose up -d

build:
	docker-compose build --pull

down:
	docker-compose down --remove-orphans

app-composer-install:
	docker-compose exec app-cli composer install

app-cli:
	docker-compose exec app-cli bash
