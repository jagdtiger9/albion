<?php

namespace Aljerom\Albion\Models;

use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;
use MagicPro\Contracts\User\SessionUserInterface;

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

    public function __construct(SessionUserInterface $user)
    {
        $this->user = $user;
        if ($this->user->uid()) {
            $this->member = (new MemberRepository())->getBy('name', $user->login());
            if ($this->member && $guildId = $this->member->getField('guildId')) {
                $this->guild = (new GuildRepository())->getById($guildId);
            }
        }
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function getUser(): SessionUserInterface
    {
        return $this->user;
    }

    public function getGuild(): ?Guild
    {
        return $this->guild;
    }
}
