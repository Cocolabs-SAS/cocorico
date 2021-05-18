# Le marché de l'inclusion
Cette version de [cocorico](https://www.cocorico.io/) est adaptée pour les besoins
spécifiques de la place de marché de l'inclusion, le _chantier 4_ de la start-up d'état
[itou](https://beta.gouv.fr/startups/itou.html).

---
# Le marché de l'inclusion

Un projet de [ITOU](https://beta.gouv.fr/startups/itou.html) visant à
valoriser l’offre commerciale des employeurs solidaires afin de développer le nombre d’emplois.

Vous pouvez le consulter sur [https://lemarche.inclusion.beta.gouv.fr/fr/](https://lemarche.inclusion.beta.gouv.fr/fr/)

## Remarques
Le marché de l'inclusion est un fork lourdement modifié de [Cocorico](https://github.com/Cocolabs-SAS/cocorico), de [CocoLabs](https://www.cocolabs.com/en/?utm_source=google&utm_medium=cpc&utm_campaign=1485295889&utm_term=cocolabs&utm_content=284163607647&campaignid=1485295889&utm_source=google&utm_medium=cpc&gclid=CjwKCAjwy42FBhB2EiwAJY0yQlR2JYmQuW93jNtEG_mGi8SC_cscrJWef06jtCAudDm8AvtMWfY0oRoCiU0QAvD_BwE).

## Documentation
La documentation initiale se trouve [ici](doc/index.md)

## Déploiement
### Démarrage des dockers
Plusieurs options sont possibles (du docker tout inclus au docker qui utilise un serveur mysql distant), la plus simple étant la combinaison docker Cocorico + docker MariaDB.
Depuis le répertoire racine de cociroco :
```
# Démarrage du docker mariadb
$ ./docker/run_local_dockers.sh
```

Démarrage du docker cocorico :
```
$ docker build -t "cocorico_local" -f ./docker/Dockerfile_Local . \
        --build-arg SQL_HOST=some-mariadb \
        --build-arg SQL_PORT=3306 \
        --build-arg SQL_USER=cocorico \
        --build-arg SQL_PASS=cocorico \
        --build-arg GGL_KEY1=[CleGoogleMaps] \
        --build-arg GGL_KEY2=[CleGoogleSpaces] \
        --build-arg SMTP_HOST="localhost" \
        --build-arg SMTP_PASSWORD="boudin_patates" \
        --build-arg SMTP_PORT="25" \
        --build-arg SMTP_USER="mayonnaise" \
&& docker run --rm -it \
        -p 9090:80 \
        --network cocorico \
        --name cocorico_local \
        -v `pwd`/src:/cocorico/src \
        -v `pwd`/web:/cocorico/web \
        -v `pwd`/app/Resources:/cocorico/app/Resources \
        cocorico_local
```
Le docker cocorico démarre sur un shell, ou l'on peut lancer webpack, symfony et effectuer l'installation suite au premier lancement (screen est disponible pour ouvrir plusieurs terminaux simultanés)
```
# Mise en place premier lancement
> ./setup

# Lancer toute la solution
> ./run_all

# Lancer webpack (css, js & assets)
> ./run_css

# Lancer symfony
> ./run
```

## Changelog
 - Fix similar listings session persisting

[CHANGELOG.md](CHANGELOG.md) list the relevant changes done for each release.

## License

Built with Cocorico, an open source platform sponsored by [Cocolabs](https://www.cocolabs.com/en/?utm_source=github&utm_medium=cocorico-page&utm_campaign=organic) to create collaborative consumption marketplaces.
Cocorico is released under the [MIT license](LICENSE).
