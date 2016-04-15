# SwiftGGAppServer

The server side of official SwiftGG Application, written in PHP.

<center>
  <img src="./logo_new.png" width ="180" alt="Logo"/>
</center>


## Introduction

The PHP version requires PHP `5.3` or greater, Because we use [Flightphp](http://flightphp.com/) framework( An extensible micro-framework for PHP ).

This project support running in all platforms.

## Installation

### Download the files.

If you're using [Composer](https://getcomposer.org/), you can run the following command:

```
composer require mikecao/flight
```

OR you can [download](https://github.com/mikecao/flight/archive/master.zip) them directly and extract them to your web directory.

### Configure your webserver.

For Apache, edit your .htaccess file with the following:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
For Nginx, add the following to your server declaration:
```

```
server {
    location / {
        try_files $uri $uri/ /index.php;
    }
}
```

## Feature

### User
- [x] Login
- [x] Register
- [x] Info
- [ ] login and register use mobile and captcha, and return the token with expire time

### Article
- [x] CategoryList
- [x] ArticleList
- [x] ArticleDetails

## Interface
Please refer to the interface [documentation](./接口规范/README.md).

## License
SwiftGGAppServer is released under the MIT license.
