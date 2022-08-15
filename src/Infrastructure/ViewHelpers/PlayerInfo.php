<?php

namespace Aljerom\Albion\Infrastructure\ViewHelpers;

use MagicPro\View\ViewHelper\AbstractViewHelper;
use Exception;
use Aljerom\Albion\Models\Member;
use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\MemberArchiveRepository;
use Aljerom\Albion\Models\Repository\MemberDailyRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;
use Aljerom\Albion\Models\CurrentMember;
use Aljerom\Albion\Models\Privilege\MemberPrivilege;
use DateInterval;
use DateTime;
use function app;

class PlayerInfo extends AbstractViewHelper
{
    /**
     * @var array
     */
    private static $container = [];

    /**
     * Список параметров, которые принимает ViewHelper с указанием соответствующих дефолтных значений
     * @return array
     */
    public function defaultParams(): array
    {
        return [
            'userId' => [
                'value' => '',
                'comment' => 'Идентификатор игрока',
                'filter' => FILTER_SANITIZE_STRING
            ],
            'userName' => [
                'value' => '',
                'comment' => 'Имя игрока',
                'filter' => FILTER_SANITIZE_STRING
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
        ];
    }

    public function getData()
    {
        $userId = $this->params['userId'];
        $userName = $this->params['userName'];
        $id = $userId ? : $userName;
        if (!isset(self::$container[$id])) {
            $data = [
                'info' => [],
                'guild' => [],
                'userPrivilege' => [],
                'guildList' => [],
            ];

            if ($id) {
                if ($userId) {
                    $player = (new MemberRepository())->getById($id);
                } else {
                    $player = (new MemberRepository())->getBy('name', $id);
                }
                if (null !== $player) {
                    $guild = (new GuildRepository())->getById($player->getField('guildId'));
                }
            } else {
                $currentUser = app(CurrentMember::class);
                $player = $currentUser->getMember();
                $guild = $currentUser->getGuild();
            }
            if (null !== $player) {
                $data['info'] = $player->getFieldMap();
                $data['stat'] = $this->getChartStat($player);

                if (null !== $guild) {
                    $data['guild'] = $guild->getFieldMap();

                    $userPrivilege = new MemberPrivilege($player);
                    $data['userPrivilege'] = [
                        'isGM' => $userPrivilege->isGM(),
                        'isOfficer' => $userPrivilege->isOfficer(),
                        'isGuardian' => $userPrivilege->isGuardian(),
                        'isRL' => $userPrivilege->isRL(),
                        'isMember' => $userPrivilege->isMember(),
                    ];
                }
                $data['guildList'] = (new MemberArchiveRepository())->getGuildList($player);
            }
            self::$container[$id] = $data;
        }

        return self::$container[$id];
    }

    private function getChartStat(Member $player)
    {
        $chartFields = [
            'killFame' => '',
            'deathFame' => '',
            'pveTotal' => '',
            'craftingTotal' => '',
            'gatheringTotal' => '',
            'fiberTotal' => '',
            'hideTotal' => '',
            'oreTotal' => '',
            'rockTotal' => '',
            'woodTotal' => '',
            'lastActive_at' => '',
        ];

        try {
            $from = (new DateTime($this->params['from']))
                //->add(new DateInterval('P1D'))
                ->sub(new DateInterval('P1D'))
                ->format('Y-m-d');
        } catch (Exception $e) {
            $from = 0;
        }
        try {
            $to = (new DateTime($this->params['to']))
                ->add(new DateInterval('P1D'))
                ->format('Y-m-d');
        } catch (Exception $e) {
            $to = 0;
        }
        $data = (new MemberDailyRepository())
            ->setReturnArray()
            ->getPlayerStat($player, $from, $to);
        if (0 === $count = count($data)) {
            return [];
        }
        if ($data[0]['timestamp']) {
            unset($data[0]);
        }

        $startDate = $data[1]['lastActive_at'];
        $endDate = $data[$count - 1]['lastActive_at'];
        $data = array_combine(
            array_column($data, 'lastActive_at'),
            $data
        );

        // Дата пересчитанных данных - начало следующего дня, 00:00:00
        $realDate = (new DateTime($startDate))
            ->sub(new DateInterval('P1D'))
            ->format(Member::UPDATED_AT_FORMAT);
        while ($startDate <= $endDate) {
            if (!isset($data[$startDate])) {
                $data[$startDate] = $chartFields;
                $data[$startDate]['lastActive_at'] = $startDate;
            } else {
                $data[$startDate] = array_intersect_key($data[$startDate], $chartFields);
            }
            $data[$startDate]['lastActive_at'] = $realDate;
            $realDate = $startDate;

            $startDate = (new DateTime($startDate))
                ->add(new DateInterval('P1D'))
                ->format(Member::UPDATED_AT_FORMAT);
        }
        ksort($data);
        $data = array_values($data);

        return $data;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getPresetTemplates(): array
    {
        return [
            'playerInfo',
        ];
    }
}
