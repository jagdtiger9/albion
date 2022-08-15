<?php

use albion\Domain\Repository\PlayerRepositoryInterface;
use albion\Domain\Repository\PlayerRewardRepositoryInterface;
use albion\Domain\Repository\ReadArchiveRepositoryInterface;
use albion\Domain\Repository\ReadPlayerRepositoryInterface;
use albion\Infrastructure\Persistence\CycleORM\PlayerRepository;
use albion\Infrastructure\Persistence\CycleORM\PlayerRewardRepository;
use albion\Infrastructure\Persistence\MySQL\ReadArchiveRepository;
use albion\Infrastructure\Persistence\MySQL\ReadPlayerRepository;

return [
    ReadPlayerRepositoryInterface::class => ReadPlayerRepository::class,
    ReadArchiveRepositoryInterface::class => ReadArchiveRepository::class,
    PlayerRepositoryInterface::class => PlayerRepository::class,
    PlayerRewardRepositoryInterface::class => PlayerRewardRepository::class,
];
