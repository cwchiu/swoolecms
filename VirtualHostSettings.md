# Apache虚拟主机配置 #
```
NameVirtualHost *
<VirtualHost *>
    ServerName swoole
    AddDefaultCharset utf-8
    DocumentRoot e:/php/swoolecms
    <Directory "e:/php/swoolecms">
        Order allow,deny
        Allow from all
    </Directory>
    <IfModule mod_rewrite.c>
         RewriteEngine on
         RewriteRule ^/([a-zA-Z]{2,10})/(index\.htm|index\.html|)$ /cms.php?app=$1                  [L]
         RewriteRule ^/([a-zA-Z]{2,10})/(\d+)\.html$ /cms.php?app=$1&id=$2                          [L]
         RewriteRule ^/([a-zA-Z]{2,10})/list_(\d+)\.html$ /cms.php?app=$1&cid=$2                    [L]
         RewriteRule ^/([a-zA-Z]{2,10})/list_(\d+)_(\d+)\.html$ /cms.php?app=$1&cid=$2&page=$3      [L]
         RewriteRule ^/([a-zA-Z\-]{1,16})\.html$ /index.php?p=$1                                    [L]
         RewriteRule ^/(ajax\.php|ajax)(.*)   /ajax.php$2                                           [L]

         RewriteRule ^/admin/(.*) /admin/$1 [L]
         RewriteRule ^/static/(.*) /static/$1 [L]
         RewriteRule ^/favicon\.ico /favicon\.ico [L]

         RewriteRule ^/swoole_plugin/(.*) /swoole_plugin/$1 [L]
         RewriteRule ^/(.*)         /index.php?_q=$1    [L,QSA]
    </IfModule>
</VirtualHost>
```

修改c:\windows\system32\drivers\etc\hosts，可以设置本地域名。