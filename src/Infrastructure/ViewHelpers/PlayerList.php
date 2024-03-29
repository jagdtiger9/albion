<?php

namespace Aljerom\Albion\Infrastructure\ViewHelpers;

use Aljerom\Albion\Domain\Exception\AlbionException;
use Aljerom\Albion\Services\GuildPlayer;
use MagicPro\Contracts\Session\FlashInterface;
use MagicPro\View\ViewHelper\AbstractViewHelper;

use function app;

class PlayerList extends AbstractViewHelper
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
                'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
            ],
            'page' => [
                'value' => 0,
                'comment' => 'Текущая страница',
                'filter' => FILTER_VALIDATE_INT
            ],
            'perPage' => [
                'value' => 100,
                'comment' => 'Кол-во записей на страницу, 0 - без ограничений',
                'filter' => FILTER_VALIDATE_INT
            ],
            'sort' => [
                'value' => 'name',
                'comment' => 'Поле сортировки списка',
                'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
            ],
            'order' => [
                'value' => 'asc',
                'comment' => 'Порядок сортировки списка',
                'filter' => ['asc', 'desc']
            ],
            'from' => [
                'value' => '',
                'comment' => 'Дата начала периода',
                'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
            ],
            'to' => [
                'value' => '',
                'comment' => 'Дата конца периода',
                'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
            ],
        ];
    }

    public function getData()
    {
        try {
            $guildPlayer = new GuildPlayer($this->params['guildName']);
            $data = $guildPlayer->setOrder($this->params['sort'], $this->params['order'])
                ->setPage($this->params['page'], $this->params['perPage'])
                ->setRange($this->params['from'], $this->params['to'])
                ->get();

            if ($newPassCode = app(FlashInterface::class)->get('newPassCode')) {
                $data['newPassCode'] = [
                    'playerId' => $newPassCode->get('id'),
                    'password' => $newPassCode->get('password'),
                ];
            }
        } catch (AlbionException $e) {
            $data['error'] = $e->getMessage();
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
            'playerList',
        ];
    }
}
