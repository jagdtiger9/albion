<?php

namespace Aljerom\Albion\Infrastructure\Persistence\CycleORM;

use MagicPro\DomainModel\Repository\CycleORM\CycleORMRepository;
use Aljerom\Albion\Domain\Entity\Identity\PlayerId;
use Aljerom\Albion\Domain\Entity\Player;
use Aljerom\Albion\Domain\Repository\PlayerRepositoryInterface;

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
