<?php

namespace Aljerom\Albion\Models;

use MagicPro\Database\Model\Model;

class EventMember extends Model
{
    public const ROLE_TANK = 'tank';

    public const ROLE_HEAL = 'heal';

    public const ROLE_DD = 'dd';

    public const ROLE_RDD = 'rdd';

    public const ROLE_MDD = 'mdd';

    public const ROLE_SUPPORT = 'support';

    public const ROLE_LIST = [
        self::ROLE_TANK => 'Tank',
        self::ROLE_HEAL => 'Healer',
        self::ROLE_DD => 'DD',
        self::ROLE_RDD => 'Range DD',
        self::ROLE_MDD => 'Melee DD',
        self::ROLE_SUPPORT => 'Support',
    ];

    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__eventMember';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fieldMap = [
        'id' => '',
        'eventId' => '',
        'memberId' => '',
        'memberName' => '',
        'role' => '',
        'isRl' => 0,
        'rlComment' => '',
        'created_at' => '',
    ];

    public function setRl()
    {
        $this->isRl = 1;

        return $this->save();
    }

    public function save()
    {
        if (!$this->created_at) {
            $this->created_at = time();
        }

        return parent::save();
    }
}
