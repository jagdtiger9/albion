<?php

namespace Aljerom\Albion\Models\Privilege;

use Aljerom\Albion\Models\CurrentMember;
use Aljerom\Albion\Models\Guild;
use Aljerom\Albion\Models\Member;
use MagicPro\Contracts\User\SessionUserInterface;

class MemberPrivilege
{
    /**
     * @var Member
     */
    protected $member;

    /**
     * @var SessionUserInterface
     */
    protected $user;

    /**
     * @var bool
     */
    private $isMember = false;

    /**
     * @var bool
     */
    private $isAdmin = false;

    public function __construct(Member $member = null)
    {
        if ($member) {
            $this->member = $member;
            if ($this->member->guildId === Guild::GUILD_ID) {
                $this->isMember = true;
            }

            $this->initPrivilege();
        } else {
            $currentUser = app(CurrentMember::class);
            $this->member = $currentUser->getMember();
            $this->user = $currentUser->getUser();
            if ($user = $currentUser->getUser()) {
                // Проверяем, если пользователь АДМИН
                $this->isAdmin = $user->isAdmin();
            }

            $this->initPrivilege();
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

    private function initPrivilege(): void
    {
        if (null === $this->member) {
            return;
        }
        if ($this->member->getField('guildId') !== Guild::GUILD_ID) {
            return;
        }
        $this->isMember = true;
    }

    public function isGM(): bool
    {
        return $this->isMember && ($this->isAdmin || $this->member->getField('gm'));
    }

    public function isOfficer(): bool
    {
        return $this->isMember && ($this->isAdmin || $this->member->getField('officer') || $this->isGM());
    }

    public function isGuardian(): bool
    {
        return $this->isMember && ($this->isAdmin || $this->member->getField('guardian') || $this->isOfficer());
    }

    public function isRL(): bool
    {
        return $this->isMember && ($this->member->getField('rl') || $this->isOfficer());
    }

    public function isMember(): bool
    {
        return $this->isMember;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
}
