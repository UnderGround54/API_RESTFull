# Run this app

### clone this app
```
clone https://github.com/UnderGround54/API_RESTFull.git
```
### Create .env.local & add
DATABASE_URL="mysql://admin:password@container_name_db:3306/database_name?serverVersion=10.11.6-MariaDB&charset=utf8mb4"
### run docker
```
docker-compose up -d
```
### Bash container
```
docker exec -it symfony_app_media_planning bash
```
### Install dependence
```
composer install
```
### Create database
```
php bin/console d:d:c
```
### Migrate database
```
php bin/console d:m:m
```
### Data fixtures
```
php bin/console d:f:l
```
### Generate key JWT
```
php bin/console lexik:jwt:generate-keypair
```
### Accede in the app
[api](http://localhost:9000/api/doc)