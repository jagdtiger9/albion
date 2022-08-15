<?php

namespace Aljerom\Albion\Models;

use common\Services\HashGenerator;
use MagicPro\Database\Model\Model;

class LoginHash extends Model
{
    public const TTL = 3600;

    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__loginHash';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = 'discordId';

    protected $fieldMap = [
        'discordId' => '',          // Дискорд ID игрока, для регистрации которого выдается ссылка
        'instantLoginHash' => '',   // Хеш для входа без пароля
        'updated_at' => 0,          // Время выдачи хеша
    ];

    public function updateHash()
    {
        if (!$this->instantLoginHash || $this->updated_at + self::TTL < time()) {
            $hash = (new HashGenerator())->getHash();
            $this->instantLoginHash = $hash;
            $this->updated_at = time();
        }

        return $this;
    }

    public function getHash(): ?string
    {
        if (!$this->instantLoginHash || $this->updated_at + self::TTL < time()) {
            return null;
        }

        return (new HashGenerator())->wrapHash(
            $this->instantLoginHash,
            $this->discordId . '.' . $this->updated_at
        );
    }
}
