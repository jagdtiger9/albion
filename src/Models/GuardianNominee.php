<?php

namespace Aljerom\Albion\Models;

use Aljerom\Albion\Models\Repository\MemberArchiveRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;

class GuardianNominee
{
    /**
     * @var Guild
     */
    private $guild;

    /**
     * @var array
     */
    private $members;

    public function __construct(Guild $guild = null)
    {
        $this->guild = $guild;

        $this->members = (new MemberRepository())
            ->asArray()
            ->getMemberList($this->guild, 'name');
    }

    /**
     * @param null $to
     * @return array
     */
    public function getList($to = null): array
    {
        $nomineeList = [];
        foreach ($this->members as $member) {
            if (!$member['guardian']) {
                $nomineeList[$member['name']] = $member;
            }
        }
        $dataIn = (new MemberArchiveRepository())
            ->asArray()
            ->getGuildMembersTo($this->guild, $to, array_keys($nomineeList));

        return array_map(
            static function ($member) use ($nomineeList) {
                $member['dayIn'] = $member['updated_at'];
                $member = array_merge($member, $nomineeList[$member['name']]);

                return $member;
            },
            $dataIn
        );
    }

    public function getMembers(): array
    {
        return $this->members;
    }
}
