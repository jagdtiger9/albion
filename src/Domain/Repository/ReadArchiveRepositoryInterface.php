<?php

namespace Aljerom\Albion\Domain\Repository;

interface ReadArchiveRepositoryInterface
{
    /**
     * @param string $guildName
     * @return array
     */
    public function getDaysInGuild(string $guildName): array;
}
