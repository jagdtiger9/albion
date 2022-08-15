<?php

namespace albion\Domain\Repository;

use albion\Domain\Entity\Identity\PlayerId;
use albion\Domain\Entity\Player;

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
