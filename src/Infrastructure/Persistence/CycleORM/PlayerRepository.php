<?php

namespace albion\Infrastructure\Persistence\CycleORM;

use MagicPro\DomainModel\Repository\CycleORM\CycleORMRepository;
use albion\Domain\Entity\Identity\PlayerId;
use albion\Domain\Entity\Player;
use albion\Domain\Repository\PlayerRepositoryInterface;

class PlayerRepository extends CycleORMRepository implements PlayerRepositoryInterface
{
    protected function getEntity(): string
    {
        return Player::class;
    }

    public function findById(PlayerId $id): ?Player
    {
        /**
         * @var Player $player
         */
        //$order = $this->select()->wherePK($uid)->load('packageService')->fetchOne();
        //$income = $this->select()->where(['uuid' => $uuid->getId()])->fetchOne();
        $player = $this->select()->wherePK($id->getId())->fetchOne();

        return $player;
    }

    public function save(Player $player): void
    {
        $this->saveEntity($player);
    }
}
