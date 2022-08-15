<?php

namespace albion\Services;

use Exception;
use albion\Domain\Exception\AlbionException;
use albion\Models\Event;
use albion\Models\Member;
use albion\Models\Repository\EventRepository;
use albion\Models\Privilege\MemberPrivilege;
use DateTime;
use albion\Models\Repository\MemberRepository;

class EventRegistration
{
    /**
     * @var Event|null
     */
    private $event;

    /**
     * @var Member
     */
    private $player;

    /**
     * @param $id
     * @return $this
     * @throws AlbionException
     */
    public function initById($id): self
    {
        $repo = new EventRepository();
        if (!$id) {
            $this->event = new Event();
        } elseif ($id && null === $this->event = $repo->getById($id)) {
            throw new AlbionException('Активность с указанным ID не найдена');
        }

        $userPrivilege = new MemberPrivilege();
        if (!$userPrivilege->isRL()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 405);
        }
        $this->player = $userPrivilege->getMember();

        return $this;
    }

    /**
     * @param $messageId
     * @param $userId
     * @return $this
     * @throws AlbionException
     */
    public function initByDiscordId($messageId, $userId): self
    {
        $repo = new EventRepository();
        if (!$messageId) {
            throw new AlbionException('Значение discordId не определено');
        }

        if (null === $this->event = $repo->getByDiscordId($messageId)) {
            $this->event = new Event();
            $this->event->discordMessageId = $messageId;
        }

        $member = (new MemberRepository())->getMainByDiscord($userId);
        if (null === $member) {
            throw new AlbionException('Необходимо авторизоваться в сервисе', 11);
        }

        $userPrivilege = new MemberPrivilege($member);
        if (!$userPrivilege->isRL()) {
            throw new AlbionException(
                'Недостаточно прав для выполнения операции, ' . $member->name . ' - ' . 405
            );
        }
        $this->player = $userPrivilege->getMember();

        return $this;
    }

    /**
     * @param $name
     * @param $type
     * @param $startDate
     * @param $startTime
     * @param $isMandatory
     * @param $factor
     * @return Event
     * @throws AlbionException
     * @throws Exception
     */
    public function saveEvent(
        $name,
        $type,
        $startDate,
        $startTime,
        $isMandatory,
        $factor
    ): Event {
        if (!$startDate) {
            throw new AlbionException('Не указано время начала активности');
        }
        $started_at = (new DateTime($startDate . ' ' . $startTime))->getTimestamp();
        if (!array_key_exists($type, Event::TYPE_LIST)) {
            throw new AlbionException('Не верный тип активности, ' . $type);
        }

        $data = [
            'name' => $name,
            'type' => $type,
            'started_at' => $started_at,
            'isMandatory' => $isMandatory,
            'factor' => $factor,
        ];
        if (!$this->event->creatorId) {
            $data = array_merge(
                $data,
                [
                    'creatorId' => $this->player->getField('id'),
                    'creatorName' => $this->player->getField('name'),
                    'guildId' => $this->player->getField('guildId'),
                    'allianceId' => $this->player->getField('allianceId'),
                ]
            );
        }
        $repo = new EventRepository($this->event);
        if (false === $repo->save($data)) {
            throw new AlbionException('Ошибка изменения активности');
        }

        return $this->event;
    }

    /**
     * @throws AlbionException
     */
    public function deleteEvent(): void
    {
        if (null === $this->event || !$this->event->id) {
            return;
        }

        $repo = new EventRepository();
        if (false === $repo->setModel($this->event)->delete()) {
            throw new AlbionException('Ошибка удаления активности');
        }
    }
}
