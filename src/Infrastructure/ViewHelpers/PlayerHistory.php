<?php

namespace Aljerom\Albion\Infrastructure\ViewHelpers;

use DateTime;
use Exception;
use MagicPro\View\ViewHelper\AbstractViewHelper;
use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\MemberArchiveRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;

class PlayerHistory extends AbstractViewHelper
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
                'comment' => 'Идентификатор гильдии',
                'filter' => FILTER_SANITIZE_STRING
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
            'playerName' => [
                'value' => '',
                'comment' => 'Имя игрока',
                'filter' => FILTER_SANITIZE_STRING
            ],
            'from' => [
                'value' => '',
                'comment' => 'Дата начала выводимой истории',
                'filter' => FILTER_SANITIZE_STRING
            ],
        ];
    }

    public function getData()
    {
        $page = $this->params['page'];
        $perPage = $this->params['perPage'];
        $from = $this->params['from'];

        $data = [
            'error' => '',
            'page' => $page,
            'perPage' => $perPage,
            'guild' => [],
            'list' => [],
            'total' => 0,
        ];

        if ($from) {
            try {
                $from = (new DateTime($from))->format('Y-m-d 00:00:00');
            } catch (Exception $e) {
                $from = '';
            }
        }
        if ($playerName = $this->params['playerName']) {
            $repository = new MemberRepository();
            if (null === $player = $repository->getBy('name', $playerName)) {
                $data['error'] = 'Игрок с указанным идентификатором не найден';

                return $data;
            }

            $guild = null;
            $total = (new MemberArchiveRepository())->getMemberHistoryTotal($player);
            $list = (new MemberArchiveRepository())
                ->setReturnArray()
                ->getMemberHistoryList($player, $perPage, $page);
        } else {
            if (false === $guildName = $this->params['guildName']) {
                $data['error'] = 'Не задан идентификатор гильдии';

                return $data;
            }
            $repository = new GuildRepository();
            if (null === $guild = $repository->getBy('name', $guildName)) {
                $data['error'] = 'Гильдия с указанным идентификатором не найдена';

                return $data;
            }

            $total = (new MemberArchiveRepository())->getGuildHistoryTotal($guild, $from);
            $list = (new MemberArchiveRepository())
                ->setReturnArray()
                ->getGuildHistoryList($guild, $from, $perPage, $page);
        }
        $data['guild'] = $guild;
        $data['list'] = $list;
        $data['total'] = $total;

        return $data;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getPresetTemplates(): array
    {
        return [
            'historyList',
        ];
    }
}
