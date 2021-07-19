[![Generic badge](https://img.shields.io/badge/ITOU-Oh_Oui-lightgreen.svg)](https://shields.io/)
[![Generic badge](https://img.shields.io/badge/État-En_Construction-yellow.svg)](https://shields.io/)
<h1 align="center">
    <img src="https://lemarche.inclusion.beta.gouv.fr/images/Logo-marche.png" />
</h1>

# Le marché de l'inclusion

Un projet de [ITOU](https://beta.gouv.fr/startups/itou.html) visant à
valoriser l’offre commerciale des employeurs solidaires afin de développer le nombre d’emplois.

Vous pouvez le consulter sur [https://lemarche.inclusion.beta.gouv.fr/fr/](https://lemarche.inclusion.beta.gouv.fr/fr/)

Les statistiques sont [publiques](https://lemarche.inclusion.beta.gouv.fr/fr/stats).

## Remarques
Le marché de l'inclusion est un fork lourdement modifié de [Cocorico](https://github.com/Cocolabs-SAS/cocorico), de [CocoLabs](https://www.cocolabs.com/en/?utm_source=google&utm_medium=cpc&utm_campaign=1485295889&utm_term=cocolabs&utm_content=284163607647&campaignid=1485295889&utm_source=google&utm_medium=cpc&gclid=CjwKCAjwy42FBhB2EiwAJY0yQlR2JYmQuW93jNtEG_mGi8SC_cscrJWef06jtCAudDm8AvtMWfY0oRoCiU0QAvD_BwE).

Parmi les modifications :
- Dernière version de la majorité des dépendances (bien que resté sous Symfony
  3.4)
- Utilisation de webpack / yarn au lieu d'assetic
- Refactor JS (webpack / Encore) et dépendances
- Retrait de MongoDB
- Évolution vers un répertoire exhaustif de toutes les structures inclusives de
  France, avec leurs offres
- Plusieurs types d'utilisateurs : Structures inclusives, Acheteurs, Partenaires
- Intégration tracker JS propre à ITOU


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
$ docker build -t "cocorico_local" -f ./docker/Dockerfile . \
        --target dev-default \
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
> ./run\_all

# Lancer webpack (css, js & assets)
> ./run\_css

# Lancer symfony
> ./run

# Provoquer une mise à jour du schéma de la base de données
# (et valider avec 'y')
> ./docker/update\_db.sh
```

## Utilisation Bootstrap v4
Un layout de base alternatif permet l'utilisation de Bootstrap 4 dans les pages du marché.

Pour l'employer, il suffit de remplacer l'entête du fichier html en question (ie: utiliser un autre layout de base), comme ci-dessous.

### Bootstrap v4
```twig
{% extends '::bs4_base.html.twig' %}

{%- block meta_title -%}
    {{ 'home.meta_title'|trans({}, 'cocorico_meta') ~ " - " ~ cocorico_site_name }}
{%- endblock -%}
```
### Bootstrap v3
```twig
{% extends '::base.html.twig' %}

{%- block meta_title -%}
    {{ 'home.meta_title'|trans({}, 'cocorico_meta') ~ " - " ~ cocorico_site_name }}
{%- endblock -%}
```

### Détails
Tous les modules et add-ons bootstrap ou jquery ne sont pas importés : ils peuvent l'être au gré des besoins
et des remplacements nécessaires.

- `web/css/bs4_import.scss` remplace `web/css/full_import.scss`
- `web/css/bs4_itou.scss` remplace `web/css/itou.scss`

Pour plus de précisions, voir `webpack.config.js` (et comparer **common** et **bs4_common**) et `web/css/bs4_import.css`.


## Changelog
 - Fix similar listings session persisting

[CHANGELOG.md](CHANGELOG.md) list the relevant changes done for each release.

## License

Built with Cocorico, an open source platform sponsored by [Cocolabs](https://www.cocolabs.com/en/?utm_source=github&utm_medium=cocorico-page&utm_campaign=organic) to create collaborative consumption marketplaces.
Cocorico is released under the [MIT license](LICENSE).

- `web/css/bs4_import.scss` remplace `web/css/full_import.scss`
- `web/css/bs4_itou.scss` remplace `web/css/itou.scss`

Pour en connaître les détails, voir `webpack.config.js` et `web/css/bs4_import.css`.


## Changelog
 - Fix similar listings session persisting

[CHANGELOG.md](CHANGELOG.md) list the relevant changes done for each release.

## License

Built with Cocorico, an open source platform sponsored by [Cocolabs](https://www.cocolabs.com/en/?utm_source=github&utm_medium=cocorico-page&utm_campaign=organic) to create collaborative consumption marketplaces.
Cocorico is released under the [MIT license](LICENSE).
