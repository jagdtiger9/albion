<?php

namespace Aljerom\Albion\Infrastructure\ViewHelpers;

use MagicPro\View\ViewHelper\AbstractViewHelper;
use Aljerom\Albion\Models\Event;
use Aljerom\Albion\Models\EventMember;
use Aljerom\Albion\Models\Repository\EventMemberRepository;
use Aljerom\Albion\Models\Repository\EventRepository;
use Aljerom\Albion\Models\Privilege\MemberPrivilege;

class EventList extends AbstractViewHelper
{
    /**
     * Список параметров, которые принимает ViewHelper с указанием соответствующих дефолтных значений
     * @return array
     */
    public function defaultParams(): array
    {
        return [
            'guildName' => [
                'value' => '',
                'comment' => 'Название гильдии',
                'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            ],
            'page' => [
                'value' => 0,
                'comment' => 'Текущая страница',
                'filter' => FILTER_VALIDATE_INT
            ],
            'perPage' => [
                'value' => 0,
                'comment' => 'Кол-во записей на страницу, 0 - без ограничений',
                'filter' => FILTER_VALIDATE_INT
            ],
            'filter' => [
                'value' => 'coming',
                'comment' => 'Тип выводимых сообщений: coming - будущие, archive - прошедшие',
                'filter' => ['all', 'coming', 'archive']
            ],
        ];
    }

    public function getData()
    {
        $data = [
            'error' => '',
        ];

        $userPrivilege = new MemberPrivilege();
        if ($userPrivilege->isMember()) {
            $player = $userPrivilege->getMember();
        } else {
            $player = null;
        }

        $total = 0;
        $page = $this->params['page'];
        $perPage = $this->params['perPage'];
        $repo = new EventRepository();
        if ($this->params['filter'] === 'archive') {
            $eventList = $repo->setReturnArray()
                ->getArchiveList($perPage, $page);
            $total = $repo->getArchiveTotal();
        } elseif ($this->params['filter'] === 'coming') {
            $eventList = $repo->setReturnArray()
                ->getComingList();
        } else {
            $eventList = $repo->setReturnArray()
                ->getList($perPage, $page);
            $total = $repo->getListTotal();
        }
        $eventIds = array_column($eventList, 'id');

        if ($eventIds) {
            $memberCount = (new EventMemberRepository())
                ->setReturnArray()
                ->getEventMemberCount($eventIds);
            $memberCount = array_combine(
                array_column($memberCount, 'eventId'),
                $memberCount
            );

            $data = [
                'list' => $eventList,
                'memberCount' => $memberCount,
                'typeList' => Event::TYPE_LIST,
                'roleList' => EventMember::ROLE_LIST,
                'registrationDelay' => Event::REGISTRATION_TTL,
                'userPrivilege' => [
                    'isGM' => $userPrivilege->isGM(),
                    'isOfficer' => $userPrivilege->isOfficer(),
                    'isGuardian' => $userPrivilege->isGuardian(),
                    'isRL' => $userPrivilege->isRL(),
                    'isMember' => $userPrivilege->isMember(),
                ],
                'player' => $player,
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
            ];
        }

        return $data;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getPresetTemplates(): array
    {
        return [
            'eventList',
        ];
    }
}
