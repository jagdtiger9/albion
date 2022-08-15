<?php

namespace Aljerom\Albion\Services;

use DateInterval;
use DateTime;
use Exception;
use Aljerom\Albion\Domain\Exception\AlbionException;
use Aljerom\Albion\Models\Member;
use Aljerom\Albion\Models\Privilege\MemberPrivilege;
use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\MemberArchiveRepository;
use Aljerom\Albion\Models\Repository\MemberDailyRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;

class GuildPlayer
{
    private $guildName;

    private $from;

    private $to;

    private $page = 0;

    private $perPage = 100;

    private $sort = 'name';

    private $order = 'asc';

    public function __construct($guildName)
    {
        $this->guildName = $guildName;
    }

    public function setOrder($sort = 'name', $order = 'asc'): self
    {
        $this->sort = $sort;
        $this->order = $order;

        return $this;
    }

    public function setRange($from = 0, $to = 0): self
    {
        $this->from = $from;
        $this->to = $to;

        return $this;
    }

    public function setPage($page = 0, $perPage = 100): self
    {
        $this->page = $page;
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @return array
     * @throws AlbionException
     */
    public function get(): array
    {
        $userPrivilege = new MemberPrivilege();
        $repository = new GuildRepository();
        if (false === $guildName = $this->guildName) {
            throw new AlbionException('Не задан идентификатор гильдии');
        }
        if (null === $guild = $repository->getBy('name', $guildName)) {
            throw new AlbionException('Гильдия с указанным идентификатором не найдена');
        }

        $dateIn = (new MemberArchiveRepository())
            ->asArray()
            ->getGuildMembersTo(
                $guild,
                (new DateTime())->format('Y-m-d 00:00:00')
            );
        $dateIn = array_map(
            static function ($item) {
                $diff = (date('U') - date('U', (new DateTime($item['updated_at']))->getTimestamp()));
                $item['daysIn'] = round($diff / 86400);

                return $item;
            },
            $dateIn
        );

        $data = [
            'page' => $this->page,
            'perPage' => $this->perPage,
            'sort' => $this->sort,
            'order' => $this->order,
            'list' => [],
            'inGuildFame' => [],
            'dateIn' => array_column($dateIn, null, 'name'),
            'total' => 0,
            'guild' => $guild,
            'roles' => Member::AUTH_LIST,
            'userPrivilege' => [
                'isGM' => $userPrivilege->isGM(),
                'isOfficer' => $userPrivilege->isOfficer(),
                'isGuardian' => $userPrivilege->isGuardian(),
                'isRL' => $userPrivilege->isRL(),
                'isMember' => $userPrivilege->isMember(),
                'isAdmin' => $userPrivilege->isAdmin(),
            ],
            'last' => [
                'month' => (new DateTime('-1 month'))->format('Y-m-d 00:00:00'),
                'three_month' => (new DateTime('-3 month'))->format('Y-m-d 00:00:00'),
                'six_month' => (new DateTime('-6 month'))->format('Y-m-d 00:00:00'),
            ],
        ];

        if (!$this->from && !$this->to) {
            $total = (new MemberRepository())->getMemberTotal($guild);
            $data['total'] = $total;
            $data['list'] = (new MemberRepository())
                ->getMemberList($guild, $this->sort, $this->order, $this->perPage, $this->page);

            $inGuildFame = (new MemberDailyRepository())
                ->getInGuildFame($guild, array_column($data['list'], 'id'));
            $inGuildFame = array_column($inGuildFame, null, 'id');
            $data['inGuildFame'] = $inGuildFame;

            return $data;
        }

        if ($from = $this->from) {
            try {
                $from = (new DateTime($from))->format('Y-m-d');
            } catch (Exception $e) {
                $from = '';
            }
        }
        if ($to = $this->to) {
            try {
                $to = (new DateTime($to))
                    ->add(new DateInterval('P1D'))
                    ->format('Y-m-d');
            } catch (Exception $e) {
                $to = '';
            }
        }

        $data['activeMembers'] = (new Member())
            ->where('guildId', $guild->getField('id'))
            ->get(['name']);
        $data['activeMembers'] = array_column($data['activeMembers'], 'name');

        $total = (new MemberDailyRepository())->getMemberTotal($guild, $from, $to);
        $data['total'] = $total;
        $params = [
            $this->sort,
            $this->order,
            $this->perPage,
            $this->page,
            $from,
            $to,
        ];
        $data['list'] = (new MemberDailyRepository())
            ->getMemberList($guild, ...$params);

        return $data;
    }
}
