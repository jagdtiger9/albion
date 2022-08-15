<?php

namespace albion\Models;

class GuildMember
{
    /**
     * @var Member
     */
    private $member;

    /**
     * @var Guild
     */
    private $guild;

    public function __construct(Guild $guild, Member $member = null)
    {
        $this->guild = $guild;

        $this->member = $member;
    }

    public function reset(Member $member): void
    {
        $member->resetGuild($this->guild);
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }
}
