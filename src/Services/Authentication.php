<?php

declare(strict_types=1);

namespace Aljerom\Albion\Services;

use MagicPro\Config\Config;

class Authentication
{
    public function __construct()
    {
        Config::get('push');
    }
}
