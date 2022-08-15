<?php

namespace Aljerom\Albion\Infrastructure\Persistence\MySQL;

use DateTime;
use MagicPro\Contracts\Database\DatabaseNewInterface;
use Aljerom\Albion\Domain\Repository\ReadArchiveRepositoryInterface;

class ReadArchiveRepository implements ReadArchiveRepositoryInterface
{
    private DatabaseNewInterface $database;

    public function __construct(DatabaseNewInterface $database)
    {
        $this->database = $database;
    }

    public function getDaysInGuild(string $guildName): array
    {
        $list = $this->database->select()
            ->columns(['name', 'max(`updated_at`) as updated_at'])
            ->where('guildIn', '1')
            ->where('guildName', $guildName)
            ->where('updated_at', '<=', (new DateTime())->format('Y-m-d 00:00:00'))
            ->groupBy('name')
            ->orderBy('updated_at');

        return array_map(
            static function ($item) {
                $diff = (date('U') - date('U', (new DateTime($item['updated_at']))->getTimestamp()));
                $item['daysIn'] = round($diff / 86400);

                return $item;
            },
            $list
        );
    }
}
