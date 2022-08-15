<?php

namespace albion\Models\Repository;

use MagicPro\Database\Model\Repository\Repository;
use albion\Models\PlayerReward;

class PlayerRewardRepository extends Repository
{
    protected $modelClass = PlayerReward::class;

    /**
     * @var PlayerReward
     */
    protected $model;
}
