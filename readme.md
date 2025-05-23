# Simple Nette API
Simple interpretation of API written in a Nette framework. API contains two entities—User and Article. JWT token provides authorization with 60 minutes of expiration time. After that time you have to login again with `/auth/login` endpoint.

OpenApi documentation is shown as homepage of the project.

## Prerequisites
- Docker
- Docker compose

## Used technologies
- PHP 8.2
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