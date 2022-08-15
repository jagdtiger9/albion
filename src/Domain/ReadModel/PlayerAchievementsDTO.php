<?php

namespace Aljerom\Albion\Domain\ReadModel;

use MagicPro\DomainModel\Dto\SimpleDto;
use payment\Domain\Entity\ValueObject\Award;

class PlayerAchievementsDTO extends SimpleDto
{
    public $id;

    public $name;

    public $guildName;

    public $discordName;

    public $discordId;

    public $isTwink;

    /**
     * @var PlayerRolesDTO
     */
    public $roles;

    public $guardian;

    public $officer;

    public $killsDone;

    public $donation;

    public $killFameTotal;

    public $deathFameTotal;

    public $pveTotalTotal;

    public $craftingTotal;

    public $gatheringTotal;

    public $smallBadge;

    public $bigBadge;

    public $medal;

    public $smallOrder;

    public $bigOrder;

    public $killSmallBadge;

    public $killMidBadge;

    public $killBigBadge;

    public $donateSmallBadge;

    public $donateMidBadge;

    public $donateBigBadge;

    /**
     * @var int
     */
    public $awardPoints;

    /**
     * @var int
     */
    public $daysInGuild;

    public function awardPoints(): int
    {
        $awards = Award::awardList();
        array_reduce(
            array_keys($awards),
            static function ($carry, $key) use ($awards) {
                return $carry + ($this->{$key} ?? 0 ? $awards[$key] : 0);
            }
        );
    }

    public function setInGuild(int $daysInGuild): void
    {
        $this->daysInGuild = $daysInGuild;
    }
}
