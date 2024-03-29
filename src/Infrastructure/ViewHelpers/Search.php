<?php

namespace Aljerom\Albion\Infrastructure\ViewHelpers;

use MagicPro\View\ViewHelper\AbstractViewHelper;
use Aljerom\Albion\Models\Guild;
use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;
use Aljerom\Albion\Services\AlbionApi;

class Search extends AbstractViewHelper
{
    /**
     * Список параметров, которые принимает ViewHelper с указанием соответствующих дефолтных значений
     * @return array
     */
    public function defaultParams(): array
    {
        return [
            'name' => [
                'value' => '',
                'comment' => 'Имя игрока, название гильдии',
                'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
            ],
        ];
    }

    public function getData()
    {
        $data = [
            'error' => '',
            'userList' => [],
            'guildList' => [],
            'apiUserList' => [],
            'apiGuildList' => [],
            'userGuilds' => [],
        ];
        if (!($name = $this->params['name'])) {
            $data['error'] = 'Не заданы параметры поиска';
        }

        $userList = (new MemberRepository())->setReturnArray()->search($name);
        if ($userList) {
            $data['userList'] = $userList;
            $guilds = (new Guild())->whereIn('id', array_column($userList, 'guildId'))
                ->get(['id', 'name']);
            if ($guilds) {
                $guilds = array_combine(array_column($guilds, 'id'), $guilds);
                $data['userGuilds'] = $guilds;
            }
        }
        $data['guildList'] = (new GuildRepository())->search($name);

        $albionApi = new AlbionApi();
        $data['apiUserList'] = $albionApi->setReturnArray()
            ->search(['q' => $name])
            ->players()
            ->get();
        $data['apiGuildList'] = $albionApi->setReturnArray()
            ->search(['q' => $name])
            ->guilds()
            ->get();

        return $data;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getPresetTemplates(): array
    {
        return [
            'search',
        ];
    }
}
