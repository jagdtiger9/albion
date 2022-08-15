<?php

namespace albion\Models;

use MagicPro\Database\Model\Model;
use albion\Domain\Exception\AlbionException;

class PlayerReward extends Model
{
    public const AWARD_POINTS = [
        'small_badge' => 1,
        'big_badge' => 3,
        'medal' => 5,
        'small_order' => 10,
        'big_order' => 25,
        'kill_small_badge' => 1,
        'kill_mid_badge' => 2,
        'kill_big_badge' => 3,
        'donate_small_badge' => 1,
        'donate_mid_badge' => 2,
        'donate_big_badge' => 3,
    ];

    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table = 'albion__playerReward';

    /**
     * Первичный ключ
     *
     * @var string
     */
    protected $primaryKey = 'discordId';

    protected $fieldMap = [
        'discordId' => '',
        'small_badge' => 0,
        'big_badge' => 0,
        'medal' => 0,
        'small_order' => 0,
        'big_order' => 0,
        'kill_small_badge' => 0,
        'kill_mid_badge' => 0,
        'kill_big_badge' => 0,
        'donate_small_badge' => 0,
        'donate_mid_badge' => 0,
        'donate_big_badge' => 0,
    ];

    /**
     * @param $reward
     * @return $this
     * @throws AlbionException
     */
    public function changeRewardStatus($reward): self
    {
        if (!isset($this->fieldMap[$reward])) {
            throw new AlbionException('Указанная награда не существует ' . $reward);
        }
        $this->{$reward} = $this->{$reward} ? 0 : 1;

        return $this;
    }
}
