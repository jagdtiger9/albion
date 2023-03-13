<?php

namespace Aljerom\Albion\Domain\Service;

use Aljerom\Albion\Domain\Entity\ReadModel\PlayerDTO;
use Aljerom\Albion\Domain\Exception\AlbionException;
use Aljerom\Albion\Domain\Repository\ReadPlayerRepositoryInterface;
use MagicPro\Contracts\User\SessionUserInterface;
use sessauth\Domain\Models\User;

class PlayerPrivilege
{
    public const GUILD_NAME = 'OCEAN';

    public const GUILD_ID = '9ovaHeVdS0KvvGnpz-uT3w';

    /**
     * @var PlayerDTO
     */
    protected $player;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var bool
     */
    private $isMember = false;

    /**
     * @var bool
     */
    private $isAdmin;

    public function __construct(
        ReadPlayerRepositoryInterface $readPlayerRepository,
        SessionUserInterface $user
    ) {
        if (null === $player = $readPlayerRepository->findByUserLogin($user->login())) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 405);
        }
        $this->user = $user;
        $this->player = $player;
        if ($player->guildId === self::GUILD_ID) {
            $this->isMember = true;
        }
        // Проверяем, если пользователь АДМИН
        $this->isAdmin = $user->isAdmin();
    }

    public function getPlayer(): ?PlayerDTO
    {
        return $this->player;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isGM(): bool
    {
        return $this->isMember && ($this->isAdmin || $this->player->gm);
    }

    public function isOfficer(): bool
    {
        return $this->isMember && ($this->isAdmin || $this->player->officer || $this->isGM());
    }

    public function isGuardian(): bool
    {
        return $this->isMember && ($this->isAdmin || $this->player->guardian || $this->isOfficer());
    }

    public function isRL(): bool
    {
        return $this->isMember && ($this->player->rl || $this->isOfficer());
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
