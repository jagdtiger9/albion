<?php

namespace albion\Infrastructure\ViewHelpers;

use MagicPro\View\ViewHelper\AbstractViewHelper;
use albion\Models\EventMember;
use albion\Models\Repository\EventMemberRepository;

class EventMemberList extends AbstractViewHelper
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
                'comment' => 'Идентификатор события',
                'filter' => FILTER_VALIDATE_INT
            ],
        ];
    }

    public function getData()
    {
        $list = [];
        if ($id = $this->params['id']) {
            $repo = new EventMemberRepository();
            $list = $repo->getByAll('eventId', $id);
        }

        $data = [
            'list' => $list,
            'roleList' => EventMember::ROLE_LIST,
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
            'eventMemberList',
        ];
    }
}
