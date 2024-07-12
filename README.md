# Run this app
### lancer docker
```
docker-compose up -d
```
### pour accéder à un shell (bash) interactif dans le conteneur
```
docker exec -it symfony_app_media_planning bash
```
### Installer dependance
```
composer install
```
### Migration base de données
```
php bin/console d:m:m
```
### Initialiser donées de base
```
php bin/console d:f:l
```
### Acceder aux application
[Swagger](http://localhost:9000/api/doc)
