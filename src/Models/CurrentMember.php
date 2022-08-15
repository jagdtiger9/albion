<?php

namespace albion\Models;

use MagicPro\Contracts\User\CurrentUserInterface;
use albion\Models\Repository\GuildRepository;
use albion\Models\Repository\MemberRepository;

class CurrentMember
{
    private $user;

    /**
     * @var Member
     */
    private $member;

    /**
     * @var Guild
     */
    private $guild;

    public function __construct(CurrentUserInterface $user)
    {
        $this->user = $user;
        if ($this->user->uid()) {
            $this->member = (new MemberRepository())->getBy('name', $user->getField('login'));
            if ($this->member && $guildId = $this->member->getField('guildId')) {
                $this->guild = (new GuildRepository())->getById($guildId);
            }
        }
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getGuild(): ?Guild
    {
        return $this->guild;
    }
}
