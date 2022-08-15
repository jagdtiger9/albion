<?php

namespace Aljerom\Albion\Models;

use MagicPro\Config\Config;

class MemberRoles
{
    public const DUCKLINGS_ROLE = 'ducklings';

    public const RECRUIT_ROLE = 'recruit';

    public const GUILD_ROLE = 'guild';

    public const ADVANCED_ROLE = 'advanced';

    public const OFFICER_ROLE = 'officer';

    public const ROLE_LIST = [
        self::DUCKLINGS_ROLE,
        self::RECRUIT_ROLE,
        self::GUILD_ROLE,
        self::RECRUIT_ROLE,
        self::ADVANCED_ROLE,
        self::OFFICER_ROLE,
    ];

    private $predefinedRoles;

    public function __construct(Config $config)
    {
        foreach (self::ROLE_LIST as $role) {
            $this->predefinedRoles[$role] = $config->{$role . 'Role'};
        }
    }

    public function getRoleList(array $roles): array
    {
        return array_filter(
            $this->predefinedRoles,
            static function ($role) use ($roles) {
                // JavaScript BigInt and JSON
                return in_array((int)$role, $roles, true);
            }
        );
    }
}
