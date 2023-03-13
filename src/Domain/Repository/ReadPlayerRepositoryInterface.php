<?php

namespace Aljerom\Albion\Domain\Repository;

use Aljerom\Albion\Domain\Entity\ReadModel\PlayerAchievementsDTO;
use Aljerom\Albion\Domain\Entity\ReadModel\PlayerDTO;

interface ReadPlayerRepositoryInterface
{
    /**
     * @param string $login
     * @return PlayerDTO|null
     */
    public function findByUserLogin(string $login): ?PlayerDTO;

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
