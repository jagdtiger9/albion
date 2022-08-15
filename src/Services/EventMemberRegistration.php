<?php

namespace albion\Services;

use albion\Domain\Exception\AlbionException;
use albion\Models\Event;
use albion\Models\EventMember;
use albion\Models\Guild;
use albion\Models\Privilege\EventPrivilege;
use albion\Models\Repository\EventMemberRepository;
use albion\Models\Repository\EventRepository;
use albion\Models\Repository\MemberRepository;

class EventMemberRegistration
{
    /**
     * @var Event|null
     */
    private $event;

    /**
     * @var EventPrivilege
     */
    private $userPrivilege;

    /**
     * @param $id
     * @return $this
     * @throws AlbionException
     */
    public function initById($id): self
    {
        $repo = new EventRepository();
        if (!$id) {
            throw new AlbionException('Не задан идентификатор активности');
        }
        if (null === $this->event = $repo->getById($id)) {
            throw new AlbionException('Активность с укзанным ID не найдена');
        }

        $this->userPrivilege = new EventPrivilege($this->event);
        if (!$this->userPrivilege->isMember()) {
            throw new AlbionException('Недостаточно прав для выполнения операции');
        }

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
            throw new AlbionException('Активность не определена');
        }

        if (null === $this->event = $repo->getByDiscordId($messageId)) {
            throw new AlbionException('Активность с укзанным ID не найдена');
        }

        $member = (new MemberRepository())->getMainByDiscord($userId, Guild::GUILD_ID);
        if (null === $member) {
            throw new AlbionException('Необходимо авторизоваться в сервисе', 11);
        }

        $this->userPrivilege = new EventPrivilege($this->event, $member);
        if (!$this->userPrivilege->isMember()) {
            throw new AlbionException(
                'Недостаточно прав для выполнения операции, member:' .
                $member->name . ' - ' . $member->guildId . '=' . Guild::GUILD_ID,
                12
            );
        }

        return $this;
    }

    /**
     * @param $role
     * @throws AlbionException
     */
    public function joinEvent($role): void
    {
        if ($this->event->isRegistrationClosed() && !$this->userPrivilege->isOfficer()) {
            throw new AlbionException('Регистрация на активность завершена', 13);
        }
        $player = $this->userPrivilege->getMember();

        $repo = new EventMemberRepository();
        $eventMember = $repo->getEventMember($this->event, $player);
        if (null !== $eventMember) {
            throw new AlbionException(
                'Вы уже зарегистрированы на активность как ' . EventMember::ROLE_LIST[$eventMember->role], 14
            );
        }

        if (false === $repo->saveJoin($this->event, $player, $role)) {
            throw new AlbionException(
                'Ошибка регистрации на активность, роль ' . EventMember::ROLE_LIST[$role], 15
            );
        }
    }

    /**
     * @param $memberId
     * @throws AlbionException
     */
    public function kickEvent($memberId): void
    {
        // Исключаем участника
        if (!$this->userPrivilege->isOfficer()) {
            throw new AlbionException('Недостаточно прав для управления');
        }

        $player = (new MemberRepository())->getById($memberId);
        $eventMember = (new EventMemberRepository())->getEventMember($this->event, $player);
        if (null === $eventMember) {
            throw new AlbionException('Игрок не является участником уктивности');
        }
        if (false === $eventMember->delete()) {
            throw new AlbionException('Ошибка исключения игрока');
        }
    }

    /**
     * @param null $role
     * @throws AlbionException
     */
    public function leaveEvent($role = null): void
    {
        // Выходим сами
        $player = $this->userPrivilege->getMember();
        $eventMember = (new EventMemberRepository())->getEventMember($this->event, $player);
        if (null === $eventMember) {
            throw new AlbionException('Вы не являетесь участником уктивности');
        }

        if ($eventMember->role !== $role) {
            throw new AlbionException('Текущая роль - ' . $eventMember->role . '; снятие роли - ' . $role);
        }

        if (false === $eventMember->delete()) {
            throw new AlbionException('Ошибка выхода из списка участников');
        }
    }
}
