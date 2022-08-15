<?php

namespace Aljerom\Albion\Models\Repository;

use MagicPro\Database\Model\Repository\Repository;
use Aljerom\Albion\Models\PlayerReward;

class PlayerRewardRepository extends Repository
{
    protected $modelClass = PlayerReward::class;

    /**
     * @var PlayerReward
     */
    protected $model;
}
