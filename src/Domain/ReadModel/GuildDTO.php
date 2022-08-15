<?php

namespace Aljerom\Albion\Domain\ReadModel;

use MagicPro\DomainModel\Dto\SimpleDto;

class GuildDTO extends SimpleDto
{
    public const GUILD_NAME = 'OCEAN';

    public const GUILD_ID = '9ovaHeVdS0KvvGnpz-uT3w';

    public $id;

    public $name;

    public $founderId;

    public $founderName;

    public $founded;

    public $allianceId = '';

    public $allianceTag = '';

    public $allianceName = '';

    public $killFame = 0;

    public $deathFame = 0;

    public $memberCount = 0;

    public $isDeleted = 0;

    public $updatePriority = 0;

    public $updatedAt = 0;
}
