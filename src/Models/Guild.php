<?php

namespace Aljerom\Albion\Models;

use MagicPro\Database\Model\Model;

class Guild extends Model
{
    public const GUILD_NAME = 'OCEAN';

    public const GUILD_ID = '9ovaHeVdS0KvvGnpz-uT3w';

    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__guilds';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fieldMap = [
        'id' => '',
        'name' => '',
        'founderId' => '',
        'founderName' => '',
        'founded' => '',
        'allianceId' => '',
        'allianceTag' => '',
        'allianceName' => '',
        'killFame' => '',
        'deathFame' => '',
        'memberCount' => '',
        'isDeleted' => 0,
        'updatePriority' => 0,
        'updatedAt' => 0,
    ];

    public function update(array $data)
    {
        $update = [];
        if ($data['AllianceId'] !== $this->allianceId) {
            $update['allianceId'] = $data['AllianceId'];
            $update['allianceTag'] = $data['AllianceTag'];
            $update['allianceName'] = $data['AllianceName'];
        }
        if ($data['MemberCount'] !== $this->memberCount) {
            $update['memberCount'] = $data['MemberCount'];
        }

        if ($update) {
            return $this->fill($update)->save();
        }

        return 0;
    }

    public function markDeleted()
    {
        $this->isDeleted = 1;

        return $this->save();
    }

    public function setUpdated()
    {
        $this->updatedAt = time();

        return $this->save();
    }
}
