<?php

namespace albion\Models;

use MagicPro\Database\Query\Raw;
use DateTime;

class EventStat
{
    private $statFrom;

    private $statTo;

    private $mandatory = 0;

    private $minRequired = 0;

    private $eventList = [];

    private $memberList = [];

    private $memberTotal = [];

    public function __construct($statFrom, $statTo)
    {
        $this->statFrom = $statFrom ? (new DateTime($statFrom))->getTimestamp() : 0;

        $this->statTo = $statTo ? (new DateTime($statTo))->getTimestamp() : 0;
    }

    public function setMandatory(int $mandatory = 0)
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    public function setMinRequired(int $minRequired = 0)
    {
        $this->minRequired = $minRequired;

        return $this;
    }

    public function process(Event $event = null)
    {
        if ($event) {
            $this->eventList = [$event->getFieldMap()];

            $this->memberList = (new EventMember())
                ->where('eventId', $event->getField('id'))
                ->get();

            $this->memberTotal = $this->memberList;
        } else {
            $eventBuilder = (new Event())->where('guildId', Guild::GUILD_ID);
            if ($this->statFrom) {
                $eventBuilder->where('started_at', '>=', $this->statFrom);
            }
            if ($this->statTo) {
                $eventBuilder->where('started_at', '<', $this->statTo);
            }
            if ($this->mandatory) {
                $eventBuilder->where('isMandatory', 1);
            }
            $this->eventList = $eventBuilder->orderBy('started_at', 'desc')->get();
            $eventIds = array_column($this->eventList, 'id');
            $this->eventList = array_combine($eventIds, $this->eventList);

            $this->memberList = (new EventMember())
                ->whereIn('eventId', $eventIds)
                ->orderBy('eventId')
                ->get();
            array_walk(
                $this->memberList,
                function ($member) {
                    if (!isset($this->eventList[$member['eventId']]['members'])) {
                        $this->eventList[$member['eventId']]['members'] = [];
                    }
                    $this->eventList[$member['eventId']]['members'][] = $member;
                }
            );
            $this->memberList = [];

            $sql = ' SELECT count(*) as count, sum(e.`factor`) as factorCount, m.`memberId`, m.`memberName` ' .
                ' FROM `albion__eventMember` m INNER JOIN `albion__event` e ON m.`eventId`=e.`id` ' .
                ' WHERE m.`eventId` in (' . implode(', ', $eventIds) . ') ' .
                ' GROUP BY m.`memberId` ORDER BY `count` DESC ';
            $this->memberTotal = app('db')->fetch($sql);

            /*$this->memberTotal = (new EventMember())
                ->whereIn('eventId', $eventIds)
                ->groupBy('memberId')
                ->orderBy('count', 'desc')
                ->get(new Raw('count(*) as count, `memberId`, `memberName`'));*/

            if ($this->minRequired) {
                $minRequiredEvents = array_reduce(
                    $this->eventList,
                    function ($carry, $item) {
                        if (isset($item['members']) && count($item['members']) >= $this->minRequired) {
                            $carry[] = $item['id'];
                        }

                        return $carry;
                    },
                    []
                );
                $minBonusEvents = array_reduce(
                    $this->eventList,
                    static function ($carry, $item) {
                        if ($item['approved']) {
                            $carry[] = $item['id'];
                        }

                        return $carry;
                    },
                    []
                );

                $memberTotalMin = [];
                if ($minRequiredEvents) {
                    $memberTotalMin = (new EventMember())
                        ->whereIn('eventId', $minRequiredEvents)
                        ->groupBy('memberId')
                        ->orderBy('count', 'desc')
                        ->get(new Raw('count(*) as count, `memberId`, `memberName`'));
                    $memberTotalMin = array_combine(
                        array_column($memberTotalMin, 'memberName'),
                        $memberTotalMin
                    );
                }

                $memberTotalBonus = (new EventMember())
                    ->whereIn('eventId', $minBonusEvents)
                    ->groupBy('memberId')
                    ->orderBy('count', 'desc')
                    ->get(new Raw('count(*) as count, `memberId`, `memberName`'));
                $memberTotalBonus = array_combine(
                    array_column($memberTotalBonus, 'memberName'),
                    $memberTotalBonus
                );

                array_walk(
                    $this->memberTotal,
                    static function (&$member) use ($memberTotalMin, $memberTotalBonus) {
                        if (isset($memberTotalMin[$member['memberName']])) {
                            $member['minRequiredCount'] = $memberTotalMin[$member['memberName']]['count'];
                        } else {
                            $member['minRequiredCount'] = 0;
                        }

                        if (isset($memberTotalBonus[$member['memberName']])) {
                            $member['approvedCount'] = $memberTotalBonus[$member['memberName']]['count'];
                        } else {
                            $member['approvedCount'] = 0;
                        }
                    }
                );
            }
        }

        return $this;
    }

    public function getTotalStat()
    {
        return [
            'events' => $this->eventList,
            'memberTotal' => $this->memberTotal,
        ];
    }

    public function getRlStat(Event $event = null)
    {
        $eventBuilder = (new Event())->where('guildId', Guild::GUILD_ID);
        if ($this->statFrom) {
            $eventBuilder->where('started_at', '>=', $this->statFrom);
        }
        if ($this->statTo) {
            $eventBuilder->where('started_at', '<', $this->statTo);
        }

        return $eventBuilder->where('approved', 1)
            ->where('rlName', '!=', '')
            ->groupBy(['rlName', 'type'])
            ->orderBy('rlName')
            ->get(new Raw('`rlName`, count(`rlName`) as rlNameCount, `type` '));
    }
}
