<?php

namespace albion\Infrastructure\ViewHelpers;

use MagicPro\View\ViewHelper\AbstractViewHelper;
use albion\Models\Event;
use albion\Models\EventMember;
use albion\Models\Guild;
use albion\Models\Privilege\EventPrivilege;
use albion\Models\Repository\EventMemberRepository;
use albion\Models\Repository\EventRepository;

class EventInfo extends AbstractViewHelper
{
    /**
     * Список параметров, которые принимает ViewHelper с указанием соответствующих дефолтных значений
     * @return array
     */
    public function defaultParams(): array
    {
        return [
            'id' => [
                'value' => 0,
                'comment' => 'Идентификатор активности',
                'filter' => FILTER_VALIDATE_INT
            ],
            'hash' => [
                'value' => 0,
                'comment' => 'Уникальнй хеш ативности',
                'filter' => FILTER_SANITIZE_STRING
            ],
        ];
    }

    public function getData()
    {
        $data = [
            'error' => '',
        ];

        if (!($hash = $this->params['hash'])) {
            $data['error'] = 'Не указан идентификатор активности';

            return $data;
        }
        $repo = new EventRepository();
        if (null === $event = $repo->getBy('linkHash', $hash)) {
            $data['error'] = 'Активность с указанным id не найдена';

            return $data;
        }

        $userPrivilege = new EventPrivilege($event);
        $player = $userPrivilege->getMember();
        if ($player) {
            $repo = new EventMemberRepository();
            $eventMember = $repo->getEventMember($event, $player) ? true : false;
        }

        $data = [
            'guildName' => Guild::GUILD_NAME,
            'player' => $player,
            'event' => $event,
            'isRegistrationClosed' => $event->isRegistrationClosed(),
            'isMember' => $eventMember ?? false,
            'typeList' => Event::TYPE_LIST,
            'roleList' => EventMember::ROLE_LIST,
            'registrationDelay' => Event::REGISTRATION_TTL,
            'userPrivilege' => [
                'isOwner' => $userPrivilege->isOwner(),
                'isGM' => $userPrivilege->isGM(),
                'isOfficer' => $userPrivilege->isOfficer(),
                'isGuardian' => $userPrivilege->isGuardian(),
                'isMember' => $userPrivilege->isMember(),
                'isRL' => $userPrivilege->isRL(),
            ],
        ];

        return $data;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getPresetTemplates(): array
    {
        return [
            'eventInfo',
        ];
    }
}
