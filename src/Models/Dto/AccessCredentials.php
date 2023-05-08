<?php

namespace Aljerom\Albion\Models\Dto;

class AccessCredentials
{
    /**
     * @var string
     */
    public $login;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $instantLoginUrl;

    public function __construct(string $login, string $password, string $instantLoginUrl)
    {
        $this->login = $login;
        $this->password = $password;
        $this->instantLoginUrl = $instantLoginUrl;
    }
}
