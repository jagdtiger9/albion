<?php

namespace albion\Domain\Entity\Identity;

use MagicPro\DomainModel\Entity\Identity\UuidIdentity;
use Webmozart\Assert\Assert;

class DiscordId extends UuidIdentity
{
    public function __construct(string $uid = '')
    {
        Assert::stringNotEmpty($uid, 'Не указан идентификатор учетной записи discord игрока');
        parent::__construct($uid);
    }
}
