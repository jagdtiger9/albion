<?php

namespace Aljerom\Albion\Domain\Entity\Identity;

use MagicPro\DomainModel\Entity\Identity\UuidIdentity;
use Webmozart\Assert\Assert;

class RewardSnapshotId extends UuidIdentity
{
    public function __construct(string $uid = '')
    {
        Assert::stringNotEmpty($uid, 'Не указан идентификатор снимка');
        parent::__construct($uid);
    }
}
