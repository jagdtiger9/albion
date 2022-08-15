<?php

namespace Aljerom\Albion\Infrastructure\ViewHelpers;

use MagicPro\View\ViewHelper\AbstractViewHelper;
use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;

class ModeratorInfo extends AbstractViewHelper
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
                'filter' => FILTER_SANITIZE_STRING
            ],
        ];
    }

    public function getData()
    {
        $data = [
            'error' => '',
        ];

        if (!($guildName = $this->params['guildName'])) {
            $data['error'] = 'Не задана гильдия';

            return $data;
        }

        if (null === $guild = (new GuildRepository())->getBy('name', $guildName)) {
            $data['error'] = 'Гильдия "' . $guildName . ' " не найдена';

            return $data;
        }

        $privileges = (new MemberRepository())->getByGuild($guild);
        $moderators = [];
        foreach ($privileges as $k => $privilege) {
            switch (true) {
                case $privilege->getField('gm'):
                    $key = 'gm';
                    break;
                case $privilege->getField('officer'):
                    $key = 'officer';
                    break;
                case $privilege->getField('guardian'):
                    $key = 'guardian';
                    break;
                default:
                    $key = 'other';
                    break;
            }
            $moderators[$key][] = $privilege;
        }
        unset($privileges);

        return [
            'moderators' => $moderators,
        ];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getPresetTemplates(): array
    {
        return [
            'moderatorInfo',
        ];
    }
}
