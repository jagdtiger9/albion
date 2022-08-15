<?php

namespace Aljerom\Albion\Models\Dto;

use MagicPro\DomainModel\Dto\Dto;

class AccessCredentials implements Dto
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
