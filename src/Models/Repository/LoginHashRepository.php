<?php

namespace Aljerom\Albion\Models\Repository;

use MagicPro\Database\Model\Repository\Repository;
use Aljerom\Albion\Models\LoginHash;

class LoginHashRepository extends Repository
{
    protected $modelClass = LoginHash::class;

    public function getByDiscordId()
    {

    }
}
