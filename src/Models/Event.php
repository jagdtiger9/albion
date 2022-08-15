<?php

namespace Aljerom\Albion\Models;

use MagicPro\Database\Model\Model;
use MagicPro\Tools\Uuid;
use Aljerom\Albion\Domain\Exception\AlbionException;

class Event extends Model
{
    public const REGISTRATION_TTL = 3 * 60 * 60;

    public const TYPE_ZVZ = 'zvz';

    public const TYPE_GANK = 'gank';

    public const TYPE_PVP = 'pvp';

    public const TYPE_FF = 'pve';

    public const TYPE_LIST = [
        self::TYPE_ZVZ => 'ЗвЗ',
        self::TYPE_GANK => 'Ганк',
        self::TYPE_PVP => 'ПвП',
        self::TYPE_FF => 'ПвЕ',
    ];

    public const PVP_TYPES = [
        self::TYPE_PVP,
        self::TYPE_ZVZ,
    ];

    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__event';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fieldMap = [
        'id' => '',
        'discordMessageId' => '',
        'linkHash' => '',
        'creatorId' => '',
        'creatorName' => '',
        'rlId' => '',
        'rlName' => '',
        'name' => '',
        'type' => '',
        'guildId' => '',
        'allianceId' => '',
        'allowAlliance' => 0,
        'started_at' => '',
        //'register_until'
        'created_at' => '',
        'isMandatory' => 1,
        'approved' => 1,
        'factor' => 1,
    ];

    /**
     * @var array - правила валидации
     */
    protected $validationRules = [
        'name' => 'required',
    ];

    /**
     * @var array - сообщения валидации
     */
    protected $validationMessages = [
        'name.required' => 'Не указан заголовок активности',
    ];

    public function setRl(Member $member = null)
    {
        $this->rlId = $member ? $member->getField('id') : '';
        $this->rlName = $member ? $member->getField('name') : '';

        return $this->save();
    }

    public function save()
    {
        if (!$this->created_at) {
            $this->created_at = time();
        }
        if (!$this->linkHash) {
            $this->linkHash = Uuid::create()->binUuid();
        }

        return parent::save();
    }

    public function isRegistrationClosed(): bool
    {
        return time() > $this->started_at + self::REGISTRATION_TTL;
    }

    public function isStarted(): bool
    {
        return $this->started_at < time();
    }

    public function setDisapproved()
    {
        $this->approved = 0;

        return $this;
    }

    public function setApproved()
    {
        $this->approved = 1;

        return $this;
    }

    /**
     * @return bool
     * @throws AlbionException
     */
    public function delete()
    {
        /*        if (time() > $this->started_at + self::REGISTRATION_TTL) {
                    throw new AlbionException(
                        'Активность не может быть удалена по истечении ' .
                        (self::REGISTRATION_TTL / 60) . 'часов с ее начала'
                    );
                }*/

        return parent::delete();
    }
}
