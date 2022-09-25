<?php

namespace Aljerom\Albion\Domain\Repository;

use Aljerom\Albion\Domain\Entity\Identity\DiscordId;
use Aljerom\Albion\Domain\Entity\PlayerReward;

interface PlayerRewardRepositoryInterface
{
    /**
     * @param DiscordId $discordId
     * @return PlayerReward|null
     */
    public function findByDiscordId(DiscordId $discordId): ?PlayerReward;
}
