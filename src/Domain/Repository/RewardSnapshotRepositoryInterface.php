<?php

namespace albion\Domain\Repository;

use albion\Domain\Entity\Identity\DiscordId;
use albion\Domain\Entity\Identity\RewardSnapshotId;
use albion\Domain\Entity\RewardSnapshot;

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
