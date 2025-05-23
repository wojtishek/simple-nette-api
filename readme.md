# Simple Nette API

## Prerequisites
- Docker
- Docker compose

## Used technologies
- PHP
- Nette Framework
- Doctrine ORM
- Codeception for testing
- Swagger for OpenApi doc

## Running app
1. Clone this repository
2. Copy `.env.example` to `.env` and change JWT_TOKEN variable to some random string.
3. Run `docker compose up -d` and wait until all containers are built
4. Visit `http://localhost:8090`, where is API documentation

## Testing
1. Have fully running docker containers
2. Run `docker exec simple_nette_api_app composer tester`