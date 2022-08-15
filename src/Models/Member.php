<?php

namespace Aljerom\Albion\Models;

use MagicPro\Database\Model\Model;
use DateTime;

class Member extends Model
{
    public const UPDATED_AT_FORMAT = 'Y-m-d 00:00:00';

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

    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__members';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fieldMap = [
        'id' => '',
        'name' => '',
        'guildId' => '',
        'guildName' => '',
        'allianceId' => '',
        'killFame' => '',
        'deathFame' => '',
        'pveTotal' => '',
        'craftingTotal' => '',
        'gatheringTotal' => '',
        'fiberTotal' => '',
        'hideTotal' => '',
        'oreTotal' => '',
        'rockTotal' => '',
        'woodTotal' => '',
        'timestamp' => '',
        'lastActive_at' => TIMESTAMP_DEFAULT,
        'activated' => 0,
        'guildIn' => 0,
        'guildOut' => '',
        'updated_at' => TIMESTAMP_DEFAULT,
        'discordName' => '',
        'discordId' => '',
        'isTwink' => 0,
        'gm' => 0,
        'officer' => 0,
        'guardian' => 0,
        'rl' => 0,
        'roles' => '',
        'killsDone' => 0,
        'donation' => 0,
    ];

    public function setActive()
    {
        $this->activated = 1;

        return $this->save();
    }

    public function setDeactive()
    {
        $this->activated = 0;

        return $this->save();
    }

    public function resetGuild(Guild $guild)
    {
        // ID ги, из которой игрок вышел
        $this->guildOut = $guild->name;
        $this->updated_at = (new DateTime('now'))
            ->format(self::UPDATED_AT_FORMAT);

        // Сбрасываем ги только в случае, если текущий ID равен ID ги
        // Может быть добавлен в предыдущем запросе в другую ги (переход)
        if ($this->guildId === $guild->id) {
            $this->guildId = '';
            $this->allianceId = '';
            $this->guildName = '';

            $this->gm = 0;
            $this->officer = 0;
            $this->guardian = 0;
            $this->rl = 0;
        }

        $this->save();
    }

    /**
     * Принадлежность пользователя к гильдии
     *
     * @param Guild $guild
     * @return bool
     */
    public function isInGuild(Guild $guild): bool
    {
        return $this->guildId === $guild->id;
    }

    public function addDiscordRegistration(DiscordRegistration $discordRegistration, Guild $guild): self
    {
        $this->discordName = $discordRegistration->discordName;
        $this->discordId = $discordRegistration->discordId;
        $this->isTwink = $discordRegistration->isTwink;

        return $this;
    }

    public function resetDiscord(): self
    {
        $this->discordName = '';
        $this->discordId = '';
        $this->isTwink = 0;
        $this->roles = '';

        return $this;
    }

    public function addDiscordInfo($discordId, $discordName, $isTwink = null, array $roles = []): self
    {
        $this->discordName = $discordName;
        // JavaScript BigInt and JSON
        $this->discordId = (string)$discordId;
        if (null !== $isTwink) {
            $this->isTwink = $isTwink;
        }
        $this->roles = json_encode($roles);

        return $this;
    }

    public function jsonSerialize()
    {
        $this->roles = json_decode($this->roles, true);

        return parent::jsonSerialize();
    }

    public function setKills($killCount)
    {
        $this->killsDone = $killCount;

        return $this;
    }

    public function setDonation($donation)
    {
        $this->donation = $donation;

        return $this;
    }
}
