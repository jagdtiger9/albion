<?php

namespace Aljerom\Albion\Infrastructure\Persistence\MySQL;

use MagicPro\Contracts\Database\DatabaseNewInterface;
use Aljerom\Albion\Domain\Entity\ReadModel\GuildDTO;
use Aljerom\Albion\Domain\Entity\ReadModel\PlayerDTO;
use Aljerom\Albion\Domain\Repository\ReadPlayerRepositoryInterface;
use Aljerom\Albion\Domain\Entity\ReadModel\PlayerAchievementsDTO;
use Aljerom\Albion\Domain\Entity\ReadModel\PlayerRolesDTO;
use sessauth\Domain\Models\User;

class ReadPlayerRepository implements ReadPlayerRepositoryInterface
{
    private DatabaseNewInterface $database;

    public function __construct(DatabaseNewInterface $database)
    {
        $this->database = $database;
    }

    public function findByUser(User $user): ?PlayerDTO
    {
        $list = $this->database->select()
            ->columns(['player.*', 'user.login'])
            ->from('albion__members as player')
            ->where('player.name', $user->login())
            ->fetchAll();
        if (!$list) {
            return null;
        }
        $player = PlayerDTO::fromArray($list[0]);
        $list = $this->database->select()
            ->columns(['guild.*'])
            ->from('albion__guilds as guild')
            ->where('guild.id', $player->guildId)
            ->fetchAll();
        if ($list) {
            $player->setGuild(GuildDTO::fromArray($list[0]));
        }

        return $player;
    }

    /**
     * @param array $criteria
     * @return int
     */
    public function getAchievementsTotal(array $criteria = []): int
    {
        // Общее кол-во игроков ги
        $select = $this->database->select()
            ->columns(['m.id'])
            ->from('albion__members as m')
            ->where(['d.`guildIn`', '!=', 1])
            ->where($criteria);

        return $select->count();
    }

    public function getAchievements(array $criteria = [], int $perPage = 100, int $offset = 0): array
    {
        $select = $this->database->select()
            ->columns(
                [
                    'm.`id`', 'm.`name`', 'm.`guildName`', 'm.`discordName`', 'm.`discordId`', 'm.`isTwink`',
                    'm.`roles`', 'm.`guardian`', 'm.`officer`', 'm.`killsDone`', 'm.`donation`',
                    'sum(d.`killFame`) as killFameTotal', 'sum(d.`deathFame`) as deathFameTotal',
                    'sum(d.`pveTotal`) as pveTotalTotal', 'sum(d.`craftingTotal`) as craftingTotal',
                    'sum(d.`gatheringTotal`) as gatheringTotal', 'r.*'
                ]
            )->from('albion__members as m')
            ->leftJoin('albion__membersDaily d')
            ->on(['m.id' => 'd.id'])
            ->leftJoin('albion__playerReward r')
            ->on(['m.discordId' => 'r.discordId'])
            ->onWhere(['m.`isTwink`' => '0'])
            ->where(['d.`guildIn`', '!=', 1])
            ->where($criteria)
            ->groupBy('d.`name`')
            ->orderBy('m.`discordName`', 'ASC')
            ->orderBy('m.`name`', 'ASC');
        if ($perPage) {
            $select->limit($perPage)->offset($offset);
        }
        $rows = $select->fetchAll();

        $list = [];
        foreach ($rows as $item) {
            $item['roles'] = PlayerRolesDTO::fromArray(json_decode($item['roles'], true) ?? []);

            $list[] = PlayerAchievementsDTO::fromArray($item);
        }

        return $list;
        /*        $sql = <<<SQL
            WHERE
            GROUP BY d.`name`
            ORDER BY IF (m.`discordName` <> '', 0, 1), m.`discordName` asc, m.`name` asc
        SQL;*/
    }
}
