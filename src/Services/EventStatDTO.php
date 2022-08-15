<?php

namespace albion\Services;

use MagicPro\DomainModel\Dto\SimpleDto;

class EventStatDTO extends SimpleDto
{
    public $id;

    public $rlName;

    public $name;

    public $type;

    public $guildId;

    public $startedAt;

    public $membersCount;

    /**
     * @var EventStatPlayersDTO[]
     */
    public $members;
}
