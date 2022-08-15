<?php

namespace albion\Models\Repository;

use MagicPro\Database\Model\Model;
use MagicPro\Database\Model\Repository\Repository;
use MagicPro\Database\Query\Raw;
use albion\Models\Event;
use albion\Models\EventMember;
use albion\Models\Member;

class EventMemberRepository extends Repository
{
    protected $modelClass = EventMember::class;

    private $time;

    public function __construct(Model $model = null)
    {
        parent::__construct($model);

        $this->time = time();
    }

    public function getEventMember(Event $event, Member $member)
    {
        return $this->builder
            ->where('eventId', $event->getField('id'))
            ->where('memberId', $member->getField('id'))
            ->first();
    }

    public function getEventMemberCount(array $ids)
    {
        return $this->builder
            ->whereIn('eventId', $ids)
            ->groupBy('eventId')
            ->get(new Raw('count(`memberId`) as count, eventId'));
    }

    public function saveJoin(Event $event, Member $member, $role = ''): bool
    {
        $data = [
            'eventId' => $event->getField('id'),
            'memberId' => $member->getField('id'),
            'memberName' => $member->getField('name'),
            'role' => $role,
        ];

        return $this->save($data);
    }

    /**
     * @param Event $event
     * @return mixed
     */
    public function delete(Event $event)
    {
        return $this->model
            ->where('eventId', $event->getField('id'))
            ->delete();
    }
}
