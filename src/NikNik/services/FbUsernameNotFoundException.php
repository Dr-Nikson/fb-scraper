<?php

namespace NikNik\services;


use Exception;

class FbUsernameNotFoundException extends \Exception
{
    public function __construct($username)
    {
        parent::__construct("No such username! $username");
    }
}