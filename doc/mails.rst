Mails
=====

Mail content are defined by two keys xxx_subject and xxx_message with xxx specific for each mail.
Each key is translated through JMS `https://cocorico.dev/[admin]/__translations/`
Translation domain is `cocorico_mail`.


Dev mode
--------

With the **[CocoricoSwiftReaderBundle](https://github.com/Cocolabs-SAS/CocoricoSwiftReaderBundle)** 
you can now consult emails send by the platform through a web interface.

By default emails send are stored in `app/spool/default` folder.
If the parameter `debug_redirects` is set to true the email send will also be displayed in the profiler.
This works only for email not send through ajax.

There are two type of mails:

- Core mails

    - The core mails has send through service `Cocorico/CoreBundle/Mailer/TwigSwiftMailer.php`.
    - New mails method must be declared in `Cocorico/CoreBundle/Mailer/MailerInterface.php`
    - Mails templates are defined in `Cocorico/CoreBundle/Resources/config/Services/mailer.yml`.

- User mails : (registration, password resetting, registration confirmation)

    - The user mails has send through service `Cocorico/UserBundle/Mailer/TwigSwiftMailer.php`
    - New mails method must be declared in `Cocorico/UserBundle/Mailer/MailerInterface.php`
    - Mails templates are defined in `Cocorico/UserBundle/Resources/config/services/mailer.xml`


Extra Bundle Routing
--------------------

To add extra bundle routing to the app add new bundle routing path to `Cocorico/CoreBundle/Routing/ExtraBundleLoader.php`


WkHtml2PDF Install
------------------

    cd mytmpfolder
    wget http://download.gna.org/wkhtmltopdf/0.12/0.12.3/wkhtmltox-0.12.3_linux-generic-amd64.tar.xz
    sudo tar xvf wkhtmltox-0.12.3_linux-generic-amd64.tar.xz
    sudo mkdir /usr/local/bin
    sudo mv wkhtmltox/bin/wkhtmlto* /usr/local/bin/