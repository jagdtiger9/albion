<?php

namespace Aljerom\Albion\Models;

use MagicPro\Database\Model\Model;

class MemberArchive extends Model
{
    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__membersArchive';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = 'uid';

    protected $fieldMap = [
        'uid' => '',
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
        'activated' => 0,
        'lastActive_at' => TIMESTAMP_DEFAULT,
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
        'killsDone' => 0,
        'donation' => 0,
    ];
}
