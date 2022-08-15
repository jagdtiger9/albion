<?php

namespace Aljerom\Albion\Models\Privilege;

use Aljerom\Albion\Models\Event;
use Aljerom\Albion\Models\Member;

class EventPrivilege extends MemberPrivilege
{
    /**
     * @var Event
     */
    private $event;

    public function __construct(Event $event, Member $member = null)
    {
        $this->event = $event;

        parent::__construct($member);
    }

    public function isOwner(): bool
    {
        return $this->member && $this->member->getField('id') === $this->event->getField('creatorId');
    }
}
