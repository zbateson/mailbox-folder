# zbateson/mailbox-folder

Shows a mailbox of emails stored as MIME messages in a folder.

![Screenshot](https://raw.githubusercontent.com/zbateson/mailbox-folder/assets/1.png)

![Screenshot 2](https://raw.githubusercontent.com/zbateson/mailbox-folder/assets/2.png)

## Requirements

Requires PHP 5.4 or newer.

## Configuration

Copy config/Prod.default.php to config/Prod.php, and edit the following values as needed.

```php
// change it if mailbox-folder sits under a different path,
// for example: '/mail'
$di->values['basepath'] = '/';
// update this if you want a different name to show up in the
// title bar
$di->values['appname'] = 'mailbox-folder';
// configure the folder to read email messages from
$di->values['maildir'] = '/path/to/mailbox/folder';
```

By default, mailbox-folder creates a simple json database using ``` jamesmoss/flywheel ```, and stores it under ``` sys_get_temp_dir() ```, in a folder called 'mailbox-folder'.  This can be changed by adding the following to config/Prod.php, along with the other configuration settings mentioned:

```php
$di->values['writedir'] = '/path/to/writable/dir';
```

#### Configuring basepath

If changing the basepath, a small modification to the .htaccess file may be needed:

```
<IfModule mod_rewrite.c>
    # turn on rewriting
    RewriteEngine On

    # turn empty requests into requests for "index.php",
    # keeping the query string intact
    RewriteRule ^$ /index.php [QSA]

    # look for cached versions of files in ./web/cache/
    RewriteCond %{DOCUMENT_ROOT}/cache%{REQUEST_URI} -f
    RewriteRule ^(.*)$ cache/$1 [L]

    # for all files not found in the file system,
    # reroute to "index.php" bootstrap script,
    # keeping the query string intact.
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !favicon.ico$

    # Replace "BASEPATH_HERE" with your basepath as set
    # above in your config
    RewriteRule ^(.*)$ /BASEPATH_HERE/index.php [QSA,L]
</IfModule>
```

## License

BSD licensed - please see [license agreement](https://github.com/zbateson/mail-mime-parser/blob/master/LICENSE).
