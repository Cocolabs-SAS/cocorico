# HTML to Twig integration

Some files and folders of markup folder have to be ignored:

    - inc/*
    - css/all.css
    - js/cors/*
    - js/lang/*
    - js/min/*
    - js/vendor/jquery.ui.widget.js
    - js/bootstrap.min.js
    - js/jquery.main.js
    - js/jquery.fileupload*
    - js/jquery-1.11.2.min.js
    - fonts/fontello.*
    - less/*
    - php/*
    - server/*
    - bower.json
    - package.json
    - Gruntfile.js

Protocol of all assets links like fonts links must be removed:

    ex: <link href='https://fonts become <link href='//fonts


1) CSS
------

**Note: Don't overwrite web/css/all.css and don't modify it**

- Copy (or overwrite) all files located in /markup/css/ folder in web/css/ or web/css/vendor folder

- Don't overwrite all-override.css copy it to all-override-2.css and add it to base.html.twig.

- New CSS images must be in web/images folder. Modify css files with new images to point to web/images folder

- Called new added css files in app/Resources/views/base.html.twig header tag

- Add or replace google fonts called in the header of app/Resources/views/base.html.twig file by those called in
/markup/css/index.html file :

    `<link href='//fonts.googleapis.com/... />`

- Move @import css files content to all-override.css then remove @import instructions from all-override css files.


2) JS
-----

**Note: Don't overwrite web/js/vendor/bootstrap.min.js neither web/js/jquery.main.js**

- If exist overwrite web/js/vendor/ie.js by the new one /markup/js/ie.js and called it in base.html.twig header tag as asset.
- Called new added js files in app/Resources/views/base.html.twig end page


3) Fonts:
---------

**Note: Don't overwrite web/fonts/fontello.**

- Copy (or overwrite) all files fonts located in (/markup/fonts/ or in markup/css/fonts/) folder in web/fonts/ folder.
Check fonts path in css files and change it to point on web/fonts/ if folder needed.


4) Images:
----------

- Copy (or overwrite) all files located in /markup/images/ folder in web/images/ folder.


5) HTML
-------

- When HTML are replaced in twig, take care of preserving existing id attributes in source file.
Most of the time they are use by Javascript functions.



6) Elements to not integrate
----------------------------

Some elements have to be ignored in term of html and js modifications :

- Calendar:
    Only css must edited and written in web/css/fullcalendar-override.css file.

- File uploads:
    No modification


