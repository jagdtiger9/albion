<?php

namespace Aljerom\Albion\Domain\Entity;

use App\DomainModel\Identity\UserId;
use MagicPro\DomainModel\Entity\AggregateRoot;
use Aljerom\Albion\Domain\Entity\Identity\RewardSnapshotId;

class RewardSnapshot extends AggregateRoot
{
    private $discordId;

    private $fixedAt;

    private $fixedByUser;

    private $isLastFix = 0;

    private $smallBadge = 0;

    private $bigBadge = 0;

    private $medal = 0;

    private $smallOrder = 0;

    private $bigOrder = 0;

    private $killSmallBadge = 0;

    private $killMidBadge = 0;

    private $killBigBadge = 0;

    private $donateSmallBadge = 0;

    private $donateMidBadge = 0;

    private $donateBigBadge = 0;

    public function __construct(
        RewardSnapshotId $snapshotId,
        UserId           $userId
    ) {
        $this->uid = $snapshotId->getId();
        $this->fixedAt = time();
        $this->fixedByUser = $userId->getId();
    }

    public function uid(): RewardSnapshotId
    {
        return new RewardSnapshotId($this->uid);
    }
}
