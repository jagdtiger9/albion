<?php

use Aljerom\Albion\Domain\Repository\PlayerRepositoryInterface;
use Aljerom\Albion\Domain\Repository\PlayerRewardRepositoryInterface;
use Aljerom\Albion\Domain\Repository\ReadArchiveRepositoryInterface;
use Aljerom\Albion\Domain\Repository\ReadPlayerRepositoryInterface;
use Aljerom\Albion\Infrastructure\Persistence\CycleORM\PlayerRepository;
use Aljerom\Albion\Infrastructure\Persistence\CycleORM\PlayerRewardRepository;
use Aljerom\Albion\Infrastructure\Persistence\MySQL\ReadArchiveRepository;
use Aljerom\Albion\Infrastructure\Persistence\MySQL\ReadPlayerRepository;

return [
    ReadPlayerRepositoryInterface::class => ReadPlayerRepository::class,
    ReadArchiveRepositoryInterface::class => ReadArchiveRepository::class,
    PlayerRepositoryInterface::class => PlayerRepository::class,
    PlayerRewardRepositoryInterface::class => PlayerRewardRepository::class,
];
