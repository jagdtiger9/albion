<?php

namespace albion\Infrastructure\ViewHelpers;

use MagicPro\View\ViewHelper\AbstractViewHelper;
use albion\Models\Repository\GuildRepository;

class GuildList extends AbstractViewHelper
{
    /**
     * Список параметров, которые принимает ViewHelper с указанием соответствующих дефолтных значений
     * @return array
     */
    public function defaultParams(): array
    {
        return [
            'guildId' => [
                'value' => '',
                'comment' => 'Идентификатор гильдии',
                'filter' => FILTER_SANITIZE_STRING
            ],
            'guildName' => [
                'value' => '',
                'comment' => 'Название гильдии',
                'filter' => FILTER_SANITIZE_STRING
            ],
        ];
    }

    public function getData()
    {
        $guildId = $this->params['guildId'];
        $guildName = $this->params['guildName'];
        if ($guildId || $guildName) {
            $repository = new GuildRepository();
            if ($guildId) {
                $guilds = $repository->getById($guildId);
            } else {
                $guilds = $repository->getBy('name', $guildName);
            }
            $guilds = $guilds ? [$guilds] : [];
        } else {
            $guilds = (new GuildRepository())->getOrderedList();
        }

        return [
            'list' => $guilds ? : [],
        ];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getPresetTemplates(): array
    {
        return [
            'guildList',
        ];
    }
}
