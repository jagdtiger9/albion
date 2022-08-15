<?php

namespace Aljerom\Albion\Domain\Repository;

use Aljerom\Albion\Domain\Entity\Identity\PlayerId;
use Aljerom\Albion\Domain\Entity\Player;

interface PlayerRepositoryInterface
{
    /**
     * @param PlayerId $id
     * @return Player|null
     */
    public function findById(PlayerId $id): ?Player;

    /**
     * @param Player $player
     */
    public function save(Player $player): void;
}
