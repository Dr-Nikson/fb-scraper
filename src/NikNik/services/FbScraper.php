<?php

namespace NikNik\services;


use Goutte\Client;
use Symfony\Component\BrowserKit\CookieJar;

/**
 * Class FbScraper
 * @package NikNik\services
 */
class FbScraper
{
    /**
     * Home page URI
     *
     * @var string
     */
    const FB_HOME = 'https://www.facebook.com/';

    /**
     * Success page URI
     *
     * @var string
     */
    const SUCCESS_URI = FbScraper::FB_HOME;

    /**
     * Login page URI
     *
     * @var string
     */
    const LOGIN_URI = 'https://www.facebook.com/login/';
    
    /**
     * Goutte client
     *
     * @var Client
     */
    protected $client;

    /**
     * FbScraper constructor.
     *
     * @param CookieJar $cookieJar pass jar if you want to restore previous session
     */
    public function __construct(CookieJar $cookieJar = null)
    {
        $this->client = new Client(array(), null, $cookieJar);
    }

    /**
     * Login to facebook.
     * Returns true if successfully authenticated
     * If you've set cookieJar before - it'll check the current auth and require new one only if it is expired
     *
     * @param string $email
     * @param string $pass
     * @return bool true if successfully authenticated
     */
    public function authenticate($email, $pass)
    {
        $loginPageCrawler = $this->client->request('GET', self::LOGIN_URI);

        if ($this->checkResponseCode() && $loginPageCrawler->getUri() === self::SUCCESS_URI) {
            return true;
        }

        $loginForm = $loginPageCrawler->filter('#loginbutton')->first()->form();
        $authData = array('email' => $email, 'pass' => $pass);

        $resultCrawler = $this->client->submit($loginForm, $authData);

        return $this->checkResponseCode() && $resultCrawler->getUri() === self::SUCCESS_URI;
    }

    /**
     * Get facebook username by given id
     *
     * @param string $id
     * @return string
     *
     * @throws FbNotAuthorizedException if authorization is expired
     * @throws FbUsernameNotFoundException if there is no username (only id)
     * @throws \Exception if something goes wrong
     */
    public function getUsername($id)
    {
        $pageCrawler =  $this->client->request('GET', self::FB_HOME . $id);
        $username = str_replace(self::FB_HOME, '', $pageCrawler->getUri());

        if (!$this->checkResponseCode() || !$this->isUsername($username)) {
            throw new \Exception('Error happens');
        }

        if ($username === (string) $id) {
            if (!$this->checkAuth()) {
                throw new FbNotAuthorizedException();
            }
            throw new FbUsernameNotFoundException($username);
        }

        return $username;
    }

    /**
     * Returns cookies that can be serialized
     *
     * @return CookieJar
     */
    public function getCookieJar() {
        return $this->client->getCookieJar();
    }

    /**
     * Checks if auth is still ok
     *
     * @return bool
     */
    public function checkAuth() {
        $loginPageCrawler = $this->client->request('GET', 'https://www.facebook.com/login/');

        return $this->checkResponseCode() && $loginPageCrawler->getUri() === self::SUCCESS_URI;
    }

    /**
     * Checks response code
     *
     * @param int $target
     * @return bool
     */
    protected function checkResponseCode($target = 200) {
        $lastResponse = $this->client->getResponse();

        return (int) $lastResponse->getStatus() === $target;
    }

    /**
     * Checks if given string is username:
     * decline strings that contains '/' or '?' characters
     *
     * @param string $username
     * @return bool
     */
    protected function isUsername($username)
    {
        return preg_match('/^[^\/\?]*$/', $username) === 1;
    }
}
