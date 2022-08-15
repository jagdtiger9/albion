<?php

namespace albion\Domain\Repository;

interface ReadArchiveRepositoryInterface
{
    /**
     * @param string $guildName
     * @return array
     */
    public function getDaysInGuild(string $guildName): array;
}
