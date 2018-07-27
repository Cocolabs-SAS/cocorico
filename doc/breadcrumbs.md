# Breadcrumbs

You need to add your breadcrumbs in `src/Cocorico/BreadcrumbBundle/Resources/content/breadcrumbs.yml`

Format:

    route_name:
        - text: 'home' # text is translatable it will be extracted to breadcrumbs.en.xliff
            route: 'route_name' #JMS I18n translatable
        - text: 'list'
            path: '#'   # it will be non route and non translatable path which will be used directly
