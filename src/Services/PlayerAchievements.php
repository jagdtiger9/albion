<?php
namespace albion\Services;

use App\DomainModel\Dto\PaginatorDto;
use albion\Domain\Entity\ReadModel\PlayerAchievementsDTO;
use albion\Domain\Entity\ReadModel\PlayerRolesDTO;
use albion\Models\PlayerReward;

class PlayerAchievements
{
    /**
     * @return int
     */
    public function getTotal(): int
    {
        $sql = <<<SQL
    SELECT m.`id` FROM `albion__members` as m 
    LEFT JOIN `albion__membersDaily` d ON m.`id`=d.`id` 
    WHERE d.`guildName`=? AND d.`guildIn`!='1'
    GROUP BY d.`id`
SQL;
        $data = app('db')->fetch($sql, ['OCEAN']);

        return count($data);
    }

    /**
     * @param PaginatorDto|null $paginator
     * @return array
     */
    public function getAchievements(?PaginatorDto $paginator = null): array
    {
        $sql = <<<SQL
    SELECT m.`id`, m.`name`, m.`guildName`, m.`discordName`, m.`discordId`, m.`isTwink`, 
        m.`roles`, m.`guardian`, m.`officer`, m.`killsDone`, m.`donation`,
        sum(d.`killFame`) as killFameTotal, sum(d.`deathFame`) as deathFameTotal, sum(d.`pveTotal`) as pveTotalTotal, 
        sum(d.`craftingTotal`) as craftingTotal, sum(d.`gatheringTotal`) as gatheringTotal,
        r.`small_badge`, r.`big_badge`, r.`medal`, r.`small_order`, r.`big_order`, r.`kill_small_badge`,
        r.`kill_mid_badge`, r.`kill_big_badge`, r.`donate_small_badge`, r.`donate_mid_badge`, r.`donate_big_badge`
    FROM `albion__members` as m 
    LEFT JOIN `albion__membersDaily` d ON m.`id`=d.`id`
    LEFT JOIN `albion__playerReward` r ON (m.`discordId`=r.`discordId` AND m.`isTwink`='0') 
    WHERE d.`guildName`=? AND d.`guildIn`!='1'
    GROUP BY d.`name`
    ORDER BY IF (m.`discordName` <> '', 0, 1), m.`discordName` asc, m.`name` asc
SQL;
        $res = app('db')->fetch($sql, ['OCEAN']);
        $data = [];
        foreach ($res ?? [] as $item) {
            $item['roles'] = PlayerRolesDTO::fromArray(json_decode($item['roles'], true) ?? []);
            $item['awardPoints'] = array_reduce(
                array_keys(PlayerReward::AWARD_POINTS),
                static function ($carry, $key) use ($item) {
                    return $carry + ($item[$key] ? PlayerReward::AWARD_POINTS[$key] : 0);
                }
            );

            $dto = PlayerAchievementsDTO::fromArray($item);
            $data[] = $dto;
        }

        return $data;
    }
}
