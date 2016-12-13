<?php

namespace NikNik\services;


use Exception;

class FbNotAuthorizedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Not authorized!');
    }
}