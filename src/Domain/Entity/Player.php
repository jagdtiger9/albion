<?php

namespace albion\Domain\Entity;

use MagicPro\DomainModel\Entity\AggregateRoot;
use payment\Domain\Entity\ValueObject\GuildName;
use payment\Domain\Entity\ValueObject\PlayerFame;
use payment\Domain\Entity\ValueObject\PlayerName;
use albion\Domain\Entity\Identity\DiscordId;
use albion\Domain\Exception\AlbionException;

class Player extends AggregateRoot
{
    private $id;

    private $name;

    private $guildId;

    private $guildName;

    private $allianceId;

    private $killFame;

    private $deathFame;

    private $pveTotal;

    private $craftingTotal;

    private $gatheringTotal;

    private $fiberTotal;

    private $hideTotal;

    private $oreTotal;

    private $rockTotal;

    private $woodTotal;

    private $timestamp = 0;

    private $lastActive_at = TIMESTAMP_DEFAULT;

    private $activated = 0;

    private $guildIn = 0;

    private $guildOut = '';

    private $updatedAt = TIMESTAMP_DEFAULT;

    private $discordName = '';

    private $discordId;

    private $isTwink = 0;

    private $gm = 0;

    private $officer = 0;

    private $guardian = 0;

    private $rl = 0;

    private $roles = 0;

    private $killsDone = 0;

    private $donation = 0;

    public function __construct(
        DiscordId  $discordId,
        PlayerName $playerName,
        GuildName  $guildName,
        PlayerFame $playerFame
    ) {
        $this->discordId = $discordId->getId();
        $this->id = $playerName->id();
        $this->name = $playerName->name();
        $this->guildId = $guildName->id();
        $this->guildName = $guildName->name();
        $this->allianceId = $guildName->allianceId();

        $this->killFame = $playerFame->killFame;
        $this->deathFame = $playerFame->deathFame;
        $this->pveTotal = $playerFame->pveTotal;
        $this->craftingTotal = $playerFame->craftingTotal;
        $this->gatheringTotal = $playerFame->gatheringTotal;
        $this->fiberTotal = $playerFame->fiberTotal;
        $this->hideTotal = $playerFame->hideTotal;
        $this->oreTotal = $playerFame->oreTotal;
        $this->rockTotal = $playerFame->rockTotal;
        $this->woodTotal = $playerFame->woodTotal;
    }

    public function discordId(): ?DiscordId
    {
        return $this->discordId ? new DiscordId($this->discordId) : null;
    }

    public function playerName(): PlayerName
    {
        return new PlayerName($this->id, $this->name);
    }

    public function changeRewardStatus($reward): self
    {
        if (!isset($this->fieldMap[$reward])) {
            throw new AlbionException('Указанная награда не существует ' . $reward);
        }
        $this->{$reward} = $this->{$reward} ? 0 : 1;

        return $this;
    }
}

