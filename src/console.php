<?php

require __DIR__ . '/../vendor/autoload.php';


use NikNik\services\FbNotAuthorizedException;
use NikNik\services\FbScraper;
use NikNik\services\FbUsernameNotFoundException;


$cookieFilename = '../cache/cookies.txt';
$cookieJar = null;

// Load cookies from file
if (is_file($cookieFilename)) {
    $cookieJar = unserialize(file_get_contents($cookieFilename));
}

$fbScraper = new FbScraper($cookieJar);

/*if (!$fbScraper->authenticate($argv[1], $argv[2])) {
    throw new Exception("Couldn't login to facebook!");
}*/


function getFbUserNames(FbScraper $scraper, $ids) {
    $userNames = array();

    foreach ($ids as $id) {
        $userName = tryToGetUserName($scraper, $id);

        if ($userName === NULL) {
            continue;
        }

        $userNames[$id] = $userName;
    }

    return $userNames;
}

/**
 * @param FbScraper $scraper
 * @param $id
 * @param bool $err
 * @return null|string
 * @throws Exception
 */
function tryToGetUserName(FbScraper $scraper, $id, $err = false)
{
    global $argv;

    try {
        $userName = $scraper->getUsername($id);
        print("id: ${id} -> ${userName} \r\n");
        return $userName;

    } catch (FbUsernameNotFoundException $e) {
        print("id: ${id} -> NOT FOUND \r\n");
        return NULL;

    } catch (FbNotAuthorizedException $ne) {
        print("id: ${id} -> NOT AUTHORIZED ... trying to login \r\n");

        if ($err || !$scraper->authenticate($argv[1], $argv[2])) {
            throw new Exception("Couldn't login to facebook!");
        }

        return tryToGetUserName($scraper, $id, true);
    }
}

$ids = explode(',', $argv[3]);
$startTime = microtime(true);
print("Start execution \r\n");

$usernames = getFbUserNames($fbScraper, $ids);

$endTime = microtime(true);
$elapsed = round($endTime - $startTime);
print("Completed. Time {$elapsed}s \r\n");

print(json_encode($usernames));


// Let's save cookies to file
$cookies = serialize($fbScraper->getCookieJar());
file_put_contents($cookieFilename, $cookies);
