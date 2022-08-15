<?php

namespace albion\Infrastructure\ViewHelpers;

use MagicPro\View\ViewHelper\AbstractViewHelper;
use albion\Models\Repository\DiscordRegistrationRepository;
use albion\Models\Repository\GuildRepository;
use albion\Models\Privilege\MemberPrivilege;
use albion\Models\Repository\MemberRepository;

class DiscordRegList extends AbstractViewHelper
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
            'filter' => [
                'value' => 'all',
                'comment' => 'all - все запросы, new - новые запросы',
                'filter' => ['new', 'all', 'registered', 'gone']
            ],
        ];
    }

    public function getData()
    {
        $data = [];
        $error = false;
        $filter = $this->params['filter'];
        $repository = new GuildRepository();
        if (false === $guildName = $this->params['guildName']) {
            $error = true;
            $data['error'] = 'Не задан идентификатор гильдии';
        }
        if (null === $guild = $repository->getBy('name', $guildName)) {
            $error = true;
            $data['error'] = 'Гильдия с указанным идентификатором не найдена';
        }

        $userPrivilege = new MemberPrivilege();
        if (!$userPrivilege->isOfficer()) {
            $error = true;
            $data['error'] = 'Недостаточно прав';
        }

        if (!$error) {
            $data['guild'] = $guild;
            if ($filter === 'new') {
                $data['list'] = (new DiscordRegistrationRepository())->asArray()->getUnconfirmed();
            } elseif ($filter === 'registered') {
                $data['list'] = (new DiscordRegistrationRepository())->asArray()->getConfirmed();
            } elseif ($filter === 'gone') {
                $data['list'] = (new MemberRepository())->asArray()->getGoneMembers($guild);
            } else {
                $data['list'] = (new DiscordRegistrationRepository())->asArray()->getAll();
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getPresetTemplates(): array
    {
        return [];
    }
}
