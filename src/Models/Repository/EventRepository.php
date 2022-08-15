<?php

namespace Aljerom\Albion\Models\Repository;

use MagicPro\Database\Model\Model;
use MagicPro\Database\Model\Repository\Repository;
use Aljerom\Albion\Domain\Exception\AlbionException;
use Aljerom\Albion\Models\Event;
use Aljerom\Albion\Models\EventMember;
use Aljerom\Albion\Models\Guild;
use Aljerom\Albion\Models\Member;

class EventRepository extends Repository
{
    protected $modelClass = Event::class;

    private $time;

    public function __construct(Model $model = null)
    {
        parent::__construct($model);

        $this->time = time();
    }

    public function getByDiscordId($discordId)
    {
        return $this->builder
            ->where('discordMessageId', $discordId)
            ->first();
    }

    public function getList($count = 100, $page = 0)
    {
        return $this->builder
            ->where('guildId', Guild::GUILD_ID)
            ->orderBy('started_at', 'desc')
            ->forPage($page, $count)
            ->get();
    }

    public function getListTotal(): int
    {
        return $this->builder
            ->where('guildId', Guild::GUILD_ID)
            ->count();
    }

    public function getComingList()
    {
        return $this->builder
            ->where('guildId', Guild::GUILD_ID)
            ->where('started_at', '>', $this->time - Event::REGISTRATION_TTL)
            ->orderBy('started_at')
            ->get();
    }

    public function getArchiveList($count = 100, $page = 0)
    {
        return $this->builder
            ->where('guildId', Guild::GUILD_ID)
            ->where('started_at', '<', $this->time - Event::REGISTRATION_TTL)
            ->orderBy('started_at')
            ->forPage($page, $count)
            ->get();
    }

    public function getArchiveTotal(): int
    {
        return $this->builder
            ->where('guildId', Guild::GUILD_ID)
            ->where('started_at', '<', $this->time - Event::REGISTRATION_TTL)
            ->count();
    }

    public function delete()
    {
        if (false === (new EventMemberRepository())->delete($this->model)) {
            throw new AlbionException(
                'Ошибка удаления активности, участники'
            );
        }

        return $this->model->delete();
    }

    public function setRl(Member $member, EventMember $eventMember)
    {
        if (false === $eventMember->where('eventId', $this->model->getField('id'))
                ->update(['isRl' => 0])) {
            throw new AlbionException('Ошибка назначения РЛ');
        }
        if (false === $eventMember->setRl()) {
            throw new AlbionException('Ошибка назначения РЛ');
        }
        if (false === $this->model->setRl($member)) {
            throw new AlbionException('Ошибка назначения РЛ');
        }
    }

    public function unsetRl(Member $member, EventMember $eventMember)
    {
        if (false === $eventMember->where('eventId', $this->model->getField('id'))
                ->update(['isRl' => 0])) {
            throw new AlbionException('Ошибка снатия роли РЛ, ' . $member->getField('name'));
        }
        if (false === $this->model->setRl()) {
            throw new AlbionException('Ошибка снатия роли РЛ, ' . $member->getField('name'));
        }
    }
}
