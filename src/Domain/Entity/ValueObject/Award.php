<?php

namespace Aljerom\Albion\Domain\Entity\ValueObject;

use Aljerom\Albion\Domain\Exception\AlbionException;

class Award
{
    public const SMALL_BADGE = 'smallBadge';

    public const BIG_BADGE = 'bigBadge';

    public const MEDAL = 'medal';

    public const SMALL_ORDER = 'smallOrder';

    public const BIG_ORDER = 'bigOrder';

    public const KILL_SMALL_BADGE = 'killSmallBadge';

    public const KILL_MID_BADGE = 'killMidBadge';

    public const KILL_BIG_BADGE = 'killBigBadge';

    public const DONATE_SMALL_BADGE = 'donateSmallBadge';

    public const DONATE_MID_BADGE = 'donateMidBadge';

    public const DONATE_BIG_BADGE = 'donateBigBadge';

    /**
     * @var string
     */
    private $award;

    /**
     * @var int
     */
    private $points;

    public function __construct(string $awardName)
    {
        $points = self::awardList()[$awardName] ?? null;
        if (null === $points) {
            throw new AlbionException('Некорректный тип награды, ' . $awardName);
        }

        $this->award = $awardName;
        $this->points = $points;
    }

    public function award(): string
    {
        return $this->award;
    }

    public function points(): int
    {
        return $this->points;
    }

    public static function awardList($keys = false): array
    {
        $list = [
            self::SMALL_BADGE => 1,
            self::BIG_BADGE => 3,
            self::MEDAL => 5,
            self::SMALL_ORDER => 10,
            self::BIG_ORDER => 25,
            self::KILL_SMALL_BADGE => 1,
            self::KILL_MID_BADGE => 2,
            self::KILL_BIG_BADGE => 3,
            self::DONATE_SMALL_BADGE => 1,
            self::DONATE_MID_BADGE => 2,
            self::DONATE_BIG_BADGE => 3,
        ];

        return $keys ? array_keys($list) : $list;
    }
}
