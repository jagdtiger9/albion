<?php

namespace Aljerom\Albion\Infrastructure\ViewHelpers;

use Aljerom\Albion\Models\Event;
use Aljerom\Albion\Models\Privilege\EventPrivilege;
use Aljerom\Albion\Models\Privilege\MemberPrivilege;
use Aljerom\Albion\Models\Repository\EventRepository;
use MagicPro\View\ViewHelper\AbstractViewHelper;

class EventForm extends AbstractViewHelper
{
    /**
     * Список параметров, которые принимает ViewHelper с указанием соответствующих дефолтных значений
     * @return array
     */
    public function defaultParams(): array
    {
        return [
            'id' => [
                'value' => '',
                'comment' => 'Идентификатор события',
                'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
            ],
        ];
    }

    public function getData()
    {
        $data = [
            'error' => '',
        ];

        // Добавлять события могут только гвардейцы и выше
        $userPrivilege = new MemberPrivilege();
        if (!$userPrivilege->isRL()) {
            $data['error'] = 'Недостаточно прав для выполнения операции';

            return $data;
        }

        $event = null;
        if ($eventId = $this->params['id']) {
            $repo = new EventRepository();
            if (null === $event = $repo->getById($eventId)) {
                $data['error'] = 'Указанная активность не найдена, id ' . $eventId;

                return $data;
            }

            $userPrivilege = new EventPrivilege($event);
            if (!$userPrivilege->isOfficer() && !$userPrivilege->isOwner()) {
                $data['error'] = 'Недостаточно прав для выполнения операции';

                return $data;
            }
        }

        $data = [
            'form_action' => '/api/albion/editEvent',
            'event' => $event,
            'fields' => [
                'id' => $event ? $event->getField('id') : 0,
                'name' => $event ? $event->getField('name') : '',
                'started_at' => $event ? $event->getField('started_at') : '',
                'allowAlliance' => $event ? $event->getField('allowAlliance') : 0,
                'type' => $event ? $event->getField('type') : '',
                'isMandatory' => $event ? $event->getField('isMandatory') : 0,
            ],
            'typeList' => Event::TYPE_LIST,
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
            'eventForm',
        ];
    }
}
