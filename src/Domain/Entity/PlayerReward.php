<?php

namespace Aljerom\Albion\Domain\Entity;

use MagicPro\DomainModel\Entity\AggregateRoot;
use payment\Domain\Entity\ValueObject\Award;
use Aljerom\Albion\Domain\Entity\Identity\DiscordId;
use Aljerom\Albion\Domain\Exception\AlbionException;

class PlayerReward extends AggregateRoot
{
    private $discordId;

    private $smallBadge = 0;

    private $bigBadge = 0;

    private $medal = 0;

    private $smallOrder = 0;

    private $bigOrder = 0;

    private $killSmallBadge = 0;

    private $killMidBadge = 0;

    private $killBigBadge = 0;

    private $donateSmallBadge = 0;

    private $donateMidBadge = 0;

    private $donateBigBadge = 0;

    public function __construct(
        DiscordId $discordId
    ) {
        $this->discordId = $discordId->getId();
    }

    public function discordId(): DiscordId
    {
        return new DiscordId($this->discordId);
    }

    public function changeAwardStatus(Award $award): self
    {
        $this->{$award->award()} = $this->{$award->award()} ? 0 : 1;

        return $this;
    }
}

