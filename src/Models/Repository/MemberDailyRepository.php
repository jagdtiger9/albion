<?php

namespace albion\Models\Repository;

use MagicPro\Database\Model\Repository\Repository;
use MagicPro\Database\Query\Raw;
use albion\Models\Guild;
use albion\Models\Member;
use albion\Models\MemberDaily;

class MemberDailyRepository extends Repository
{
    protected $modelClass = MemberDaily::class;

    /**
     * @var MemberDaily
     */
    protected $model;

    public function getLastUpdate(): string
    {
        $record = $this->builder
            ->orderBy('lastActive_at', 'desc')
            ->limit(1)
            ->get(['lastActive_at']);

        return $record ? $record[0]->getField('lastActive_at') : '';
    }

    public function getMemberTotal(Guild $guild, $from = '', $to = ''): int
    {
        if ($guild->getField('id')) {
            $this->builder->where('guildId', $guild->getField('id'));
        }
        if ($from) {
            $this->builder->where('lastActive_at', '>', $from);
        }
        if ($to) {
            $this->builder->where('lastActive_at', '<=', $to);
        }
        $result = $this->builder->groupBy('id')->get(['id']);

        return count($result);
    }

    public function getMemberList(Guild $guild, ...$params)
    {
        [$sort, $order, $perPage, $page, $from, $to] = $params;

        if ($guild->getField('id')) {
            $this->builder->where('guildId', $guild->getField('id'));
        }
        if ($from) {
            $this->builder->where('lastActive_at', '>', $from);
        }
        if ($to) {
            $this->builder->where('lastActive_at', '<=', $to);
        }
        $this->builder->where('guildIn', '!=', 1);

        $query = '`id`, `name`, `guildId`, `guildName`, `allianceId`, `activated`, sum(`killFame`) as killFame, ' .
            'sum(`deathFame`) as deathFame, sum(`pveTotal`) as pveTotal, sum(`craftingTotal`) as craftingTotal, ' .
            'sum(`gatheringTotal`) as gatheringTotal, sum(`fiberTotal`) as fiberTotal, sum(`hideTotal`) as hideTotal, ' .
            'sum(`oreTotal`) as oreTotal, sum(`rockTotal`) as rockTotal, sum(`woodTotal`) as woodTotal';

        return $this->builder
            ->orderBy($sort, $order)
            ->forPage($page, $perPage)
            ->groupBy('id')
            ->get(new Raw($query));
    }

    public function getInGuildFame(Guild $guild, array $memberIds = [])
    {
        $this->builder
            ->where('guildId', $guild->getField('id'))
            ->whereIn('id', $memberIds)
            ->where('guildIn', '!=', 1);

        $query = '`id`, `name`, `guildId`, `guildName`, sum(`killFame`) as killFame, ' .
            'sum(`deathFame`) as deathFame, sum(`pveTotal`) as pveTotal, sum(`craftingTotal`) as craftingTotal, ' .
            'sum(`gatheringTotal`) as gatheringTotal ';

        return $this->builder
            ->groupBy('id')
            ->get(new Raw($query));
    }

    public function getPlayerStat(Member $member, $from, $to)
    {
        $builder = $this->builder->where('id', $member->getField('id'));
        if ($from) {
            $builder->where('lastActive_at', '>', $from);
        }
        if ($to) {
            $builder->where('lastActive_at', '<=', $to);
        }

        return $builder->get();
    }
}
