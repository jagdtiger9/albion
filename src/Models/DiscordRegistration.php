<?php

namespace Aljerom\Albion\Models;

use MagicPro\Database\Model\Model;
use sessauth\Domain\Models\User;

class DiscordRegistration extends Model
{
    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__discordRegistration';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = ['discordId', 'albionName'];

    protected $fieldMap = [
        'discordId' => '',      // ID игрока в discord
        'discordName' => '',    // Имя игрока в discord
        'albionName' => '',     // Имя игрока
        'albionId' => '',       //
        'isTwink' => 0,
        'guildName' => '',      //
        'moderator' => '',      // Имя игрока, подтвердившего заявку
        'registered_at' => 0,   // Время регистрации
        'confirm_at' => 0,     //
    ];

    public function register($discordName, Member $member, bool $isTwink): self
    {
        $this->discordName = $discordName;
        $this->isTwink = $isTwink ? 1 : 0;
        $this->albionId = $member->id;
        $this->guildName = $member->guildName;
        $this->registered_at = time();

        return $this;
    }

    public function confirm(User $user)
    {
        $this->confirm_at = time();
        $this->moderator = $user->uid;

        return $this;
    }

    public function isConfirmed(): bool
    {
        return $this->confirm_at > 0;
    }
}
