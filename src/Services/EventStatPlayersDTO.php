<?php
namespace albion\Services;

use MagicPro\DomainModel\Dto\SimpleDto;

class EventStatPlayersDTO extends SimpleDto
{
    public $memberName;

    public $role;

    public $discordName;
}
