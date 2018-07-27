# Mails


Mail content are defined by two keys xxx_subject and xxx_message with xxx specific for each mail.
Each key is translated through JMS `http://cocorico.local/[admin]/__translations/`
Translation domain is `cocorico_mail`.


## Dev mode

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