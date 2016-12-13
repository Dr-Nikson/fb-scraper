<?php

require __DIR__ . '/../vendor/autoload.php';


use NikNik\services\FbScraper;


$cookieFilename = '../cache/cookies.txt';
$cookieJar = null;

// Load cookies from file
if (is_file($cookieFilename)) {
    $cookieJar = unserialize(file_get_contents($cookieFilename));
}

$fbScraper = new FbScraper($cookieJar);

if (!$fbScraper->authenticate('USERNAME', 'PASWORD')) {
    throw new Exception("Couldn't login to facebook!");
}


// should return username "game.nik.nik"
echo $fbScraper->getUsername('1263646880362901');

// should return error
// echo $fbScraper->getUsername('1263646880362901123');

// Let's save cookies to file
$cookies = serialize($fbScraper->getCookieJar());
file_put_contents($cookieFilename, $cookies);

