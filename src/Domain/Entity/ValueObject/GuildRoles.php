<?php

namespace Aljerom\Albion\Domain\Entity\ValueObject;

class GuildRoles
{
    public const AUTH_GM = 'gm';

    public const AUTH_OFFICER = 'officer';

    public const AUTH_GUARDIAN = 'guardian';

    public const AUTH_RL = 'rl';

    public const AUTH_LIST = [
        self::AUTH_GM,
        self::AUTH_OFFICER,
        self::AUTH_GUARDIAN,
        self::AUTH_RL,
    ];
}
