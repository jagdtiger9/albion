<?php

namespace Aljerom\Albion\Domain\ReadModel;

use MagicPro\DomainModel\Dto\SimpleDto;

class PlayerDTO extends SimpleDto
{
    public const UPDATED_AT_FORMAT = 'Y-m-d 00:00:00';

    public $id;

    public $name;

    public $guildId;

    public $guildName;

    public $allianceId;

    public $killFame = 0;

    public $deathFame = 0;

    public $pveTotal = 0;

    public $craftingTotal = 0;

    public $gatheringTotal = 0;

    public $fiberTotal = 0;

    public $hideTotal = 0;

    public $oreTotal = 0;

    public $rockTotal = 0;

    public $woodTotal = 0;

    public $timestamp = 0;

    public $lastActive_at = TIMESTAMP_DEFAULT;

    public $activated = 0;

    public $guildIn = 0;

    public $guildOut = '';

    public $updated_at = TIMESTAMP_DEFAULT;

    public $discordName = '';

    public $discordId = '';

    public $isTwink = 0;

    public $gm = 0;

    public $officer = 0;

    public $guardian = 0;

    public $rl = 0;

    public $roles = 0;

    public $killsDone = 0;

    public $donation = 0;

    /**
     * @var GuildDTO
     */
    public $guild;

    public function setGuild(GuildDTO $guild): void
    {
        $this->guild = $guild;
    }
}
