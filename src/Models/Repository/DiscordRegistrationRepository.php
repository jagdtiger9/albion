<?php

namespace Aljerom\Albion\Models\Repository;

use MagicPro\Database\Model\Repository\Repository;
use Aljerom\Albion\Models\DiscordRegistration;

class DiscordRegistrationRepository extends Repository
{
    protected $modelClass = DiscordRegistration::class;

    public function getUnconfirmed()
    {
        return $this->builder->where('confirm_at', 0)->get();
    }

    public function getConfirmed()
    {
        return $this->builder->where('confirm_at', '!=', 0)->get();
    }
}
