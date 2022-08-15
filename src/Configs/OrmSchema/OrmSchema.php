<?php

use Cycle\ORM\Mapper\Mapper;
use Cycle\ORM\Schema;
use albion\Domain\Entity\Player;
use albion\Domain\Entity\PlayerReward;

/**
 * Primary_KEY uuid, если сделать uid, то не работает добавление нового агрегата PackagePayment с добавлением
 * PackageOptionPayment
 * ошибка - Unable to complete: Cycle\ORM\Command\Database\Insert(packagePaymentId)
 */
return [
    Player::class => [
        Schema::ROLE => 'player',
        Schema::ENTITY => Player::class,
        Schema::MAPPER => Mapper::class, // default POPO mapper
        Schema::DATABASE => 'default',
        Schema::TABLE => 'albion__members',
        Schema::PRIMARY_KEY => 'id',
        Schema::COLUMNS => [
            'id' => 'id',  // property => column
            'name',
            'guildId',
            'guildName',
            'allianceId',
            'killFame',
            'deathFame',
            'pveTotal',
            'craftingTotal',
            'gatheringTotal',
            'fiberTotal',
            'hideTotal',
            'oreTotal',
            'rockTotal',
            'woodTotal',
            'timestamp',
            'lastActive_at',
            'activated',
            'guildIn',
            'guildOut',
            'updatedAt' => 'updated_at',
            'discordName',
            'discordId',
            'isTwink',
            'gm',
            'officer',
            'guardian',
            'rl',
            'roles',
            'killsDone',
            'donation',
        ],
        Schema::TYPECAST => [],
        Schema::RELATIONS => [],
    ],
    PlayerReward::class => [
        Schema::ROLE => 'recipients',
        Schema::ENTITY => PlayerReward::class,
        Schema::MAPPER => Mapper::class,
        Schema::DATABASE => 'default',
        Schema::TABLE => 'albion__playerReward',
        Schema::PRIMARY_KEY => 'discordId',
        Schema::COLUMNS => [
            'discordId' => 'discordId',  // property => column
            'smallBadge' => 'small_badge',
            'big_badge',
            'medal',
            'smallOrder' => 'small_order',
            'bigOrder' => 'big_order',
            'killSmallBadge' => 'kill_small_badge',
            'killMidBadge' => 'kill_mid_badge',
            'killBigBadge' => 'kill_big_badge',
            'donateSmallBadge' => 'donate_small_badge',
            'donateMidBadge' => 'donate_mid_badge',
            'donateBigBadge' => 'donate_big_badge',
        ],
        Schema::TYPECAST => [],
        Schema::RELATIONS => []
    ],
];
