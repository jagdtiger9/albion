<?php

namespace albion\Domain\Repository;

use albion\Domain\Entity\ReadModel\PlayerAchievementsDTO;
use albion\Domain\Entity\ReadModel\PlayerDTO;
use sessauth\Domain\Models\User;

interface ReadPlayerRepositoryInterface
{
    /**
     * @param User $user
     * @return PlayerDTO|null
     */
    public function findByUser(User $user): ?PlayerDTO;

    /**
     * @param array $criteria
     * @return int
     */
    public function getAchievementsTotal(array $criteria = []): int;

    /**
     * @param array $criteria
     * @param int $perPage
     * @param int $offset
     * @return PlayerAchievementsDTO[]
     */
    public function getAchievements(array $criteria = [], int $perPage = 100, int $offset = 0): array;
}
