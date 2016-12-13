<?php

namespace NikNik\services;


use Goutte\Client;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\DomCrawler\Form;

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
        $loginPageResult = $this->request('GET', self::LOGIN_URI);

        if ($this->checkResponseCode($loginPageResult->response)
            && $loginPageResult->crawler->getUri() === self::SUCCESS_URI) {

            return true;
        }

        $loginForm = $loginPageResult->crawler->filter('#loginbutton')->first()->form();
        $authData = array('email' => $email, 'pass' => $pass);

        $submitResult = $this->submit($loginForm, $authData);

        return $this->checkResponseCode($submitResult->response)
            && strpos($submitResult->crawler->getUri(), '/login') === false;
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
        $pageResult =  $this->request('GET', self::FB_HOME . $id);
        $username = str_replace(self::FB_HOME, '', $pageResult->crawler->getUri());

        if (!$this->checkResponseCode($pageResult->response) || !$this->isUsername($username)) {
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
        $loginPageResult = $this->request('GET', 'https://www.facebook.com/login/');

        return $this->checkResponseCode($loginPageResult->response)
            && $loginPageResult->crawler->getUri() === self::SUCCESS_URI;
    }

    /**
     * @param $method
     * @param $uri
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param null $content
     * @param bool $changeHistory
     * @return FbRequestResult
     * @internal param array ...$params
     */
    protected function request(
        $method, $uri, $parameters = array(),
        $files = array(), $server = array(), $content = null, $changeHistory = true)
    {
        $crawler = $this->client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $response = $this->client->getResponse();

        return new FbRequestResult($crawler, $response);
    }


    /**
     * @param Form $form
     * @param array $values
     * @return FbRequestResult
     * @internal param array ...$params
     */
    protected function submit($form, $values = array())
    {
        $crawler =  $this->client->submit($form, $values);
        $response = $this->client->getResponse();

        return new FbRequestResult($crawler, $response);
    }

    /**
     * Checks response code
     *
     * @param $lastResponse
     * @param int $target
     * @return bool
     */
    protected function checkResponseCode($lastResponse, $target = 200) {
//        $lastResponse = $this->client->getResponse();

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
