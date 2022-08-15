<?php

namespace Aljerom\Albion\Domain\Entity\Identity;

use MagicPro\DomainModel\Entity\Identity\UuidIdentity;
use Webmozart\Assert\Assert;

class PlayerId extends UuidIdentity
{
    public function __construct(string $uid = '')
    {
        Assert::stringNotEmpty($uid, 'Не указан идентификатор игрока');
        parent::__construct($uid);
    }
}
