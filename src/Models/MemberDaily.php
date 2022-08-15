<?php

namespace albion\Models;

use MagicPro\Database\Model\Model;

class MemberDaily extends Model
{
    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__membersDaily';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fieldMap = [
        'uid' => 0,
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

    ];
}
