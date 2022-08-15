<?php

namespace Aljerom\Albion\Models;

use MagicPro\Database\Model\Model;

class MemberArchiveTmp extends Model
{
    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__membersArchiveTmp';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = 'uid';

    protected $fieldMap = [
        'uid' => 0,
        'id' => '',
        'name' => '',
        'guildId' => '',
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
    ];
}
