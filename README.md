# NikNik/FbScraper

This is scraper example. Allows to get username by id from facebook.

## Installation

Run `composer install`

Change username and password to your own

```php
if (!$fbScraper->authenticate('USERNAME', 'PASWORD')) {
```

After run `php src/console.php`

## Caching

You can get cookies and save them to DB or even file:

```php
$cookies = serialize($fbScraper->getCookieJar());
file_put_contents($cookieFilename, $cookies);
```

After it's done - you can just load it and pass to the constructor:

```php
$cookieJar = unserialize(file_get_contents($cookieFilename));
$fbScraper = new FbScraper($cookieJar);
```

Scraper will check current authorization. If it's ok - there will be no additional login requests
