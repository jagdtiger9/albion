<?php

namespace albion\Models\Repository;

use MagicPro\Database\Model\Repository\Repository;
use albion\Models\LoginHash;

class LoginHashRepository extends Repository
{
    protected $modelClass = LoginHash::class;

    public function getByDiscordId()
    {

    }
}
