# Virtual host sample

## Dev

- HTTP 

    .. code:: ApacheConf

        <VirtualHost 127.0.0.1:80>
            ServerName cocorico.local
            ServerAlias cocorico.local

            #For multiple images uploads
            LimitRequestBody 240000000

            DocumentRoot /var/www/cocorico.local/Symfony/web
            <Directory /var/www/cocorico.local/Symfony/web>
                #For performance and security reasons we should not use htaccess in prod
                AllowOverride Indexes FileInfo AuthConfig
                Order Allow,Deny
                Allow from all
            </Directory>
        </VirtualHost>


- HTTPS

    .. code:: ApacheConf

        <VirtualHost 127.0.0.1:80>
            ServerName cocorico.local
            ServerAlias cocorico.local
            Redirect permanent / https://cocorico.local/
        </VirtualHost>

        <VirtualHost 127.0.0.1:443>
            ServerName cocorico.local
            ServerAlias cocorico.local

            SSLEngine on
            SSLCertificateFile "/etc/ssl/certs/cocorico.pem"
            SSLCertificateKeyFile "/etc/ssl/certs/private.key"

            DocumentRoot /var/www/cocorico.local/Symfony/web
            <Directory /var/www/cocorico.local/Symfony/web>
                #For performance reason we should not use htaccess
                AllowOverride Indexes FileInfo AuthConfig
                Order Allow,Deny
                Allow from all
            </Directory>
        </VirtualHost>


## Prod

- HTTPS

    .. code:: ApacheConf
    
        <Directory /var/www/vhosts/cocorico.prod/httpdocs/Symfony/web>
            DirectoryIndex app.php
            AllowOverride None
        
            LimitRequestBody 240000000
        
            <Files ~ "^\.ht">
                Order deny,allow
                Deny from all
            </Files>
        
            <IfModule mod_rewrite.c>
                RewriteEngine On
        
                RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
                RewriteRule ^(.*) - [E=BASE:%1]
        
                RewriteCond %{HTTP:Authorization} .
                RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
        
                RewriteCond %{ENV:REDIRECT_STATUS} ^$
                RewriteRule ^app\.php(/(.*)|$) %{ENV:BASE}/$2 [R=301,L]
        
                RewriteCond %{REQUEST_FILENAME} -f
                RewriteRule .? - [L]
    
                RewriteRule .? %{ENV:BASE}/app.php [L]
            </IfModule>
    
            <IfModule !mod_rewrite.c>
                <IfModule mod_alias.c>
                    RedirectMatch 302 ^/$ /app.php/
                </IfModule>
            </IfModule>
    
            <IfModule mod_expires.c>
                ExpiresActive on
    
                ExpiresByType image/jpg "access plus 60 days"
                ExpiresByType image/png "access plus 60 days"
                ExpiresByType image/gif "access plus 60 days"
                ExpiresByType image/jpeg "access plus 60 days"
    
                ExpiresByType text/css "access plus 1 days"
    
                ExpiresByType image/x-icon "access plus 1 month"
    
                ExpiresByType application/pdf "access plus 1 month"
                ExpiresByType audio/x-wav "access plus 1 month"
                ExpiresByType audio/mpeg "access plus 1 month"
                ExpiresByType video/mpeg "access plus 1 month"
                ExpiresByType video/mp4 "access plus 1 month"
                ExpiresByType video/quicktime "access plus 1 month"
                ExpiresByType video/x-ms-wmv "access plus 1 month"
                ExpiresByType application/x-shockwave-flash "access 1 month"
    
                ExpiresByType text/javascript "access plus 1 week"
                ExpiresByType application/x-javascript "access plus 1 week"
                ExpiresByType application/javascript "access plus 1 week"
    
                ExpiresByType application/vnd.bw-fontobject "access plus 30 days"
                ExpiresByType application/x-font-ttf "access plus 30 days"
                ExpiresByType application/x-woff "access plus 30 days"  
            </IfModule>
    
            AddOutputFilterByType DEFLATE text/html text/css application/x-javascript application/x-shockwave-flash
            # Cope with proxies
            Header append Vary User-Agent env=!dont-vary
            # Cope with several bugs in IE6
            BrowserMatch "\bMSIE 6" !no-gzip !gzip-only-text/html
        </Directory>
    
        <Directory /var/www/vhosts/cocorico.prod/httpdocs/Symfony/web/uploads>
            Deny from all
    
            <Files ^(*.jpeg|*.jpg|*.png|*.gif|*.pdf)>
                Order deny,allow
                Allow from all
            </Files>
    
            <Files ~ "^\.ht">
                Order deny,allow
                Deny from all
            </Files>
        </Directory>
    
        <IfModule mod_fcgid.c>
            IPCCommTimeout          180
            IPCConnectTimeout       180
        </IfModule>