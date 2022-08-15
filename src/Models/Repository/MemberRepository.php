<?php

namespace albion\Models\Repository;

use MagicPro\Database\Model\Repository\Repository;
use DateTime;
use albion\Models\Guild;
use albion\Models\Member;

class MemberRepository extends Repository
{
    protected $modelClass = Member::class;

    /**
     * @var Member
     */
    protected $model;

    public function getMemberTotal(Guild $guild)
    {
        if ($guild->getField('id')) {
            $this->builder->where('guildId', $guild->getField('id'));
        }

        return $this->builder->count();
    }

    public function getByGuild(Guild $guild)
    {
        return $this->builder
            ->where('guildId', $guild->getField('id'))
            ->orderBy('name')
            ->get();
    }

    public function getDiscordOfficer($discordId)
    {
        return $this->builder
            ->where('discordId', $discordId)
            ->where('officer', 1)
            ->first();
    }

    public function getMainByDiscord($discordId, $guildId = '')
    {
        $builder = $this->builder
            ->where('discordId', $discordId)
            ->where('isTwink', 0);
        if ($guildId) {
            $builder = $builder->where('guildId', $guildId);
        }
        return $builder->first();
    }

    public function getMemberList(Guild $guild, $orderBy = '', $order = '', $count = 500, $page = 0)
    {
        $builder = $this->builder;
        if ($guild->getField('id')) {
            $builder = $builder->where('guildId', $guild->getField('id'));
        }

        return $builder->orderBy($orderBy, $order)
            ->forPage($page, $count)
            ->get();
    }

    public function saveMember(Member $member, $data, $joinIn = false): bool
    {
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }

        $data['id'] = $data['Id'];
        $data['name'] = $data['Name'];
        $data['guildId'] = $data['GuildId'];
        $data['guildName'] = $data['GuildName'];
        $data['allianceId'] = $data['AllianceId'];
        $data['killFame'] = $data['KillFame'];
        $data['deathFame'] = $data['DeathFame'];
        $data['pveTotal'] = $data['LifetimeStatistics']['PvE']['Total'];
        $data['craftingTotal'] = $data['LifetimeStatistics']['Crafting']['Total'];
        $data['gatheringTotal'] = $data['LifetimeStatistics']['Gathering']['All']['Total'];
        $data['fiberTotal'] = $data['LifetimeStatistics']['Gathering']['Fiber']['Total'];
        $data['hideTotal'] = $data['LifetimeStatistics']['Gathering']['Hide']['Total'];
        $data['oreTotal'] = $data['LifetimeStatistics']['Gathering']['Ore']['Total'];
        $data['rockTotal'] = $data['LifetimeStatistics']['Gathering']['Rock']['Total'];
        $data['woodTotal'] = $data['LifetimeStatistics']['Gathering']['Wood']['Total'];
        $data['timestamp'] = $data['LifetimeStatistics']['Timestamp'] ?? '';
        if ($data['timestamp']) {
            $data['lastActive_at'] = (new DateTime($data['timestamp']))
                ->format(Member::UPDATED_AT_FORMAT);
        } else {
            $data['lastActive_at'] = '0000-00-00 00:00:00';
        }
        $data['updated_at'] = (new DateTime('now'))
            ->format(Member::UPDATED_AT_FORMAT);

        if ($member->getField('id')) {
            if ($data['GuildId'] != $member->getField('guildId')) {
                // Пользователь перешел из какой-то ги
                $data['guildIn'] = 1;
                // Указываем предыдущую ги (если сначала обработано добавление, будет ги; если выход, то будет пустота)
                $data['guildOut'] = $member->getField('guildName');
            } else {
                // Пользователь остается в текущей ги, обнуляем guildOut, чтобы не тянулось постоянно
                $data['guildIn'] = 0;
                $data['guildOut'] = '';
            }
        } else {
            // Незарегенный пользователь пришел в ги - 1, добавлена новая ги - 0
            // Может быть вышедший из АФК, но определить это нереально (пришел из другой или из АФК)
            $data['guildIn'] = $joinIn ? 1 : 0;
        }

        return $member->fill($data)->save();
    }

    public function search($name, Guild $guild = null): array
    {
        $builder = $this->builder
            ->where('name', 'like', $name . '%');
        if ($guild) {
            $builder->where('guildId', $guild->getField('id'));
        }

        return $builder->get();
    }

    public function getApiGoneMembers(Guild $guild, array $activeListNames)
    {
        return $this->builder
            ->where('guildId', $guild->getField('id'))
            ->whereNotIn('name', $activeListNames)
            ->get();
    }

    public function getGoneMembers(Guild $guild)
    {
        return $this->builder
            ->where('guildId', '!=', $guild->getField('id'))
            ->where('discordName', '!=', '')
            ->get();
    }
}
