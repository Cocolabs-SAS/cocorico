## Migration Bootstrap 4
Avec la migration prévue vers un backend django, et l'intégration d'un design système ITOU,
il est intéressant de reviser les composants front existants (et utiles) du C4 pour leur transfert futur.

Une version "bs4" du gabarit de base permet de passer en bootstrap 4.

La différence se fait au niveau webpack (voir `webpack.config.js`), qui compile les différents ensembles.


### Passer de bootstrap 3 vers 4
#### Bootstrap v4
```twig
{% extends '::bs4_base.html.twig' %}

{%- block meta_title -%}
    {{ 'home.meta_title'|trans({}, 'cocorico_meta') ~ " - " ~ cocorico_site_name }}
{%- endblock -%}
```

#### Bootstrap v3
```twig
{% extends '::base.html.twig' %}

{%- block meta_title -%}
    {{ 'home.meta_title'|trans({}, 'cocorico_meta') ~ " - " ~ cocorico_site_name }}
{%- endblock -%}
```


### Composants
Plusieurs composants de page ont ainsi leur version "bootstrap 4" (préfixe `bs_4`):


- Layout: src/Cocorico/CoreBundle/Resources/views/Frontend/bs4\_layout.html.twig
- Footer: src/Cocorico/CoreBundle/Resources/views/Frontend/Common/\_bs4\_footer.html.twig
- Header: src/Cocorico/CoreBundle/Resources/views/Frontend/Common/\_bs4\_header.html.twig
- ...

Niveau importation CSS, ces deux fichiers gèrent l'importation des principaux fichiers :
- pour bootstrap v3 /web/css/final_import.scss
- pour bootstrap v4 /web/css/bs4_import.scss

Si jamais des ajouts doivent être effectués, les fichiers suivants sont à disposition:
- pour bootstrap v3 /web/css/itou.css
- pour bootstrap v4 /web/css/bs4_itou.css


### Exemple
Exemple de première page "BS4", la page "C'est quoi l'inclusion" :
- src/Cocorico/CoreBundle/Resources/views/Fronted/Itou/inclusion.html.twig
- En local: http://127.0.0.1:9090/fr/itou/inclusion
