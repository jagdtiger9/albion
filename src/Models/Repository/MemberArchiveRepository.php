<?php

namespace Aljerom\Albion\Models\Repository;

use MagicPro\Database\Model\Repository\Repository;
use DateTime;
use Exception;
use MagicPro\Database\Query\Raw;
use Aljerom\Albion\Models\Guild;
use Aljerom\Albion\Models\Member;
use Aljerom\Albion\Models\MemberArchive;

class MemberArchiveRepository extends Repository
{
    protected $modelClass = MemberArchive::class;

    /**
     * @var MemberArchive
     */
    protected $model;

    /**
     * Получение даты следующего обновления с указанного момента времени
     * Если дата следующего обновления не установлена и последняя дата обновления сегодняшняя, продолжаем обновлять
     * могут быть заторможенные апдейты API
     *
     * @param string $lastTime
     * @return string
     * @throws Exception
     */
    public function getNextUpdate($lastTime = ''): string
    {
        $currentTime = (new DateTime())->format(Member::UPDATED_AT_FORMAT);

        $record = $this->builder
            ->orderBy('lastActive_at')
            ->limit(1)
            ->where('lastActive_at', '>', $lastTime)
            ->get(['lastActive_at']);

        if ($record) {
            return $record[0]->getField('lastActive_at');
        }
        if ($lastTime === $currentTime) {
            return $lastTime;
        }

        return '';
    }

    public function getGuildHistoryTotal(Guild $guild, $from = null): int
    {
        $builder = $this->builder
            ->where('guildIn', '1')
            ->where('guildId', $guild->getField('id'));
        if ($from) {
            $builder = $builder->where('updated_at', '>=', $from);
        }

        return $builder->orWhere(
            static function ($query) use ($guild, $from) {
                $query->where('guildOut', $guild->getField('name'));
                if ($from) {
                    $query->where('updated_at', '>=', $from);
                }
            }
        )->count();
    }

    /**
     * Получение списка записей игроков присоединяющихся-выходящих из ги
     *
     * @param Guild $guild
     * @param string|null $from
     * @param int $count
     * @param int $page
     * @return array
     */
    public function getGuildHistoryList(Guild $guild, $from = null, $count = 100, $page = 0): array
    {
        $builder = $this->builder
            ->where('guildIn', '1')
            ->where('guildId', $guild->getField('id'));
        if ($from) {
            $builder = $builder->where('updated_at', '>=', $from);
        }

        $builder->orWhere(
            static function ($query) use ($guild, $from) {
                $query->where('guildOut', $guild->getField('name'));
                if ($from) {
                    $query->where('updated_at', '>=', $from);
                }
            }
        )->orderBy('updated_at', 'desc');

        if ($count) {
            $builder->forPage($page, $count);
        }

        return $builder->get();
    }

    /**
     * Получение списка записей игроков кандидатов в гвардейцы
     *
     * @param Guild $guild
     * @param null $to
     * @param array $activeMemberIds
     * @return array
     */
    public function getGuildMembersTo(Guild $guild, $to = null, array $activeMemberIds = []): array
    {
        $builder = $this->builder
            ->where('guildIn', '1')
            ->where('guildName', $guild->name)
            ->where('updated_at', '<=', $to)
            ->groupBy('name')
            ->orderBy('updated_at');
        if ($activeMemberIds) {
            $builder = $builder->whereIn('name', $activeMemberIds);
        }

        return $builder->get(new Raw('`name`, max(`updated_at`) as updated_at'));
    }

    public function getMemberHistoryTotal(Member $member): int
    {
        $builder = $this->builder
            ->where('name', $member->getField('name'));

        return $builder->orWhere(
            static function ($query) {
                $query->where('guildOut', '!=', '');
                $query->where('guildIn', 1);
            }
        )->count();
    }

    /**
     * Получение списка записей игроков присоединяющихся-выходящих из ги
     *
     * @param Member $member
     * @param int $count
     * @param int $page
     * @return array
     */
    public function getMemberHistoryList(Member $member, $count = 100, $page = 0): array
    {
        $builder = $this->builder
            ->where('name', $member->getField('name'));

        $builder->where(
            static function ($query) {
                $query->where('guildOut', '!=', '');
                $query->orWhere('guildIn', 1);
            }
        )->orderBy('uid');

        if ($count) {
            $builder->forPage($page, $count);
        }

        return $builder->get();
    }

    public function getGuildList(Member $member): array
    {
        $data = $this->builder
            ->where('name', $member->getField('name'))
            ->groupBy('guildId')
            ->get(['guildName']);

        return $data ? array_column($data, 'guildName') : [];
    }
}
