<?php

namespace albion\Services;

use InvalidArgumentException;
use albion\Domain\Exception\AlbionException;
use albion\Models\Privilege\MemberPrivilege;
use albion\Models\Repository\MemberRepository;
use albion\Models\Repository\PlayerRewardRepository;

class EventStat
{
    /**
     * @param $from
     * @param $to
     * @return array
     */
    public function getList($from): array
    {
        $sql = <<<SQL
    SELECT e.`id`, e.`rlName`, e.`name`, e.`type`, e.`guildId`, e.`started_at`, count(m.`memberId`) as membersCount
    FROM `albion__event` as e
    LEFT JOIN `albion__eventMember` m ON e.`id`=m.`eventId`
    WHERE e.`started_at` > ?
    GROUP BY m.`eventId`
    ORDER BY e.`started_at` desc
SQL;
        $res = app('db')->fetch($sql, [$from]);
        $data = [];
        foreach ($res ?? [] as $item) {
            $item['members'] = $this->getMembers($item['id']);
            $dto = EventStatDTO::fromArray(
                $item,
                static function ($k, $v) {
                    return $k === 'started_at' ? date('d.m.Y H:i', $v) : $v;
                }
            );
            $data[] = $dto;
        }

        return $data;
    }

    public function getMembers($eventId): array
    {
        $sql = <<<SQL
    SELECT  ml.`memberName`, ml.`role`, m.`discordName`
    FROM `albion__eventMember` ml
    LEFT JOIN `albion__members` m ON ml.`memberId`=m.`id`
    WHERE ml.`eventId`=? 
    ORDER BY ml.`memberName` asc
SQL;
        $res = app('db')->fetch($sql, [$eventId]);

        return array_map(
            static function ($item) {
                return EventStatPlayersDTO::fromArray($item);
            },
            $res
        );
    }

    public function setAchievement($playerId, $achievement)
    {
        $userPrivilege = new MemberPrivilege();
        if (!$userPrivilege->isOfficer()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 405);
        }

        $repo = new MemberRepository();
        if (!$playerId || null === $player = $repo->getById($playerId)) {
            throw new InvalidArgumentException('Игрок с указанным ID (' . $playerId . ') не найден');
        }

        if (!$player->discordId) {
            throw new InvalidArgumentException('У игрока ' . $player->name . ' не привязан дискорд');
        }

        $playerReward = (new PlayerRewardRepository())->findOrInit($player->discordId);
        $playerReward->changeRewardStatus($achievement);
        if (false === $playerReward->save()) {
            throw new AlbionException('Ошибка изменения статуса награды');
        }

        return $playerReward;
    }
}
