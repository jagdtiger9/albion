<?php

namespace Aljerom\Albion\Infrastructure\ViewHelpers;

use MagicPro\View\ViewHelper\AbstractViewHelper;
use DateInterval;
use DateTime;
use Exception;
use Aljerom\Albion\Models\Event;
use Aljerom\Albion\Models\EventMember;
use Aljerom\Albion\Models\Guild;
use Aljerom\Albion\Models\Privilege\MemberPrivilege;
use Aljerom\Albion\Models\Repository\EventRepository;
use Aljerom\Albion\Models\EventStat as EventStatModel;

class EventStat extends AbstractViewHelper
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
            'from' => [
                'value' => 0,
                'comment' => 'Начало период формирования статистики',
                'filter' => FILTER_SANITIZE_STRING
            ],
            'to' => [
                'value' => 0,
                'comment' => 'Конец периода формирования статистики',
                'filter' => FILTER_SANITIZE_STRING
            ],
            'mandatory' => [
                'value' => 0,
                'comment' => 'Только обязательные активности',
                'filter' => [0, 1]
            ],
            'minRequired' => [
                'value' => 0,
                'comment' => 'Необходимое кол-во участников, чтобы активность была учтена',
                'filter' => FILTER_VALIDATE_INT
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

        if ($to = $this->params['to']) {
            try {
                $to = (new DateTime($to))
                    ->add(new DateInterval('P1D'))
                    ->format('Y-m-d');
            } catch (Exception $e) {
                $to = 0;
            }
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $to = $to > $now ? $now : $to;
        }

        if ($id = $this->params['id']) {
            $repo = new EventRepository();
            $event = $repo->getById($id);
            if ($event->getField('guildId') !== $player->getField('guildId')) {
                $data['error'] = 'Недостаточно прав';

                return $data;
            }

            $eventStat = new EventStatModel($this->params['from'], $to);
            $stat = $eventStat->process($event)->getTotalStat();

            $rlStat = $eventStat->getRlStat($event);
        } else {
            $eventStat = new EventStatModel($this->params['from'], $to);
            $stat = $eventStat->setMandatory($this->params['mandatory'])
                ->setMinRequired($this->params['minRequired'])
                ->process()
                ->getTotalStat();

            $rlStat = $eventStat->getRlStat();
        }

        $data = [
            'userPrivilege' => [
                'isGM' => $userPrivilege->isGM(),
                'isOfficer' => $userPrivilege->isOfficer(),
                'isGuardian' => $userPrivilege->isGuardian(),
                'isRL' => $userPrivilege->isRL(),
                'isMember' => $userPrivilege->isMember(),
            ],
            'guildName' => Guild::GUILD_NAME,
            'player' => $player,
            'stat' => $stat,
            'rlStat' => $rlStat,
            'typeList' => Event::TYPE_LIST,
            'roleList' => EventMember::ROLE_LIST,
            'pvpTypes' => Event::PVP_TYPES,
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
            'eventStat',
        ];
    }
}
