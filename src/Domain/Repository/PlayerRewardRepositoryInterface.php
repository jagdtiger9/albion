<?php

namespace albion\Domain\Repository;

use albion\Domain\Entity\Identity\DiscordId;
use albion\Domain\Entity\PlayerReward;

interface PlayerRewardRepositoryInterface
{
    /**
     * @param DiscordId $discordId
     * @return PlayerReward|null
     */
    public function findByDiscordId(DiscordId $discordId): ?PlayerReward;

    /**
     * @param PlayerReward $playerReward
     */
    public function save(PlayerReward $playerReward): void;
}
