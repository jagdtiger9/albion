<?php

namespace albion\Infrastructure\ViewHelpers;

use DateTime;
use Exception;
use MagicPro\View\ViewHelper\AbstractViewHelper;
use albion\Models\Repository\GuildRepository;
use albion\Models\GuardianNominee as GuardianNomineeModel;

class GuardianNominee extends AbstractViewHelper
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
            'to' => [
                'value' => '',
                'comment' => 'Дата начала выводимой истории',
                'filter' => FILTER_SANITIZE_STRING
            ],
        ];
    }

    public function getData()
    {
        $data = [
            'error' => '',
            'guild' => [],
            'list' => [],
        ];

        if ($to = $this->params['to']) {
            try {
                $to = (new DateTime($to))->format('Y-m-d 00:00:00');
            } catch (Exception $e) {
                $to = '';
            }
        } else {
            $to = (new DateTime())->format('Y-m-d 00:00:00');
        }

        if (false === $guildName = $this->params['guildName']) {
            $data['error'] = 'Не задан идентификатор гильдии';

            return $data;
        }
        $repository = new GuildRepository();
        if (null === $guild = $repository->getBy('name', $guildName)) {
            $data['error'] = 'Гильдия с указанным идентификатором не найдена';

            return $data;
        }

        $guardianNominee = new GuardianNomineeModel($guild);
        $list = $guardianNominee->getList($to);

        $data['guild'] = $guild;
        $data['list'] = $list;
        $data['members'] = $guardianNominee->getMembers();

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
