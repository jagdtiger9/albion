<?php

namespace Aljerom\Albion\Infrastructure\Persistence\CycleORM;

use MagicPro\DomainModel\Repository\CycleORM\CycleORMRepository;
use Aljerom\Albion\Domain\Entity\Identity\DiscordId;
use Aljerom\Albion\Domain\Entity\PlayerReward;
use Aljerom\Albion\Domain\Repository\PlayerRewardRepositoryInterface;

class PlayerRewardRepository extends CycleORMRepository implements PlayerRewardRepositoryInterface
{
    protected function getEntity(): string
    {
        return PlayerReward::class;
    }

    public function findByDiscordId(DiscordId $discordId): ?PlayerReward
    {
        /**
         * @var PlayerReward $playerReward
         */
        $playerReward = $this->select()->wherePK($discordId->getId())->fetchOne();

        return $playerReward;
    }

    public function save(PlayerReward $playerReward): void
    {
        $this->saveEntity($playerReward);
    }
}
