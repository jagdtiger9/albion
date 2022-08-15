<?php

namespace Aljerom\Albion\Domain\Repository;

use Aljerom\Albion\Domain\Entity\Identity\DiscordId;
use Aljerom\Albion\Domain\Entity\Identity\RewardSnapshotId;
use Aljerom\Albion\Domain\Entity\RewardSnapshot;

interface RewardSnapshotRepositoryInterface
{
    /**
     * @return RewardSnapshotId
     */
    public function nextIdentity(): RewardSnapshotId;

    /**
     * @param RewardSnapshotId $uuid
     * @return ?RewardSnapshot
     */
    public function findById(RewardSnapshotId $uuid): ?RewardSnapshot;

    /**
     * @param DiscordId $discordId
     * @return RewardSnapshot[]
     */
    public function findByDiscordId(DiscordId $discordId): array;
}
