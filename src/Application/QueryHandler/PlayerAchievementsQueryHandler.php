<?php

namespace Aljerom\Albion\Application\QueryHandler;

use App\DomainModel\Dto\PagedResultDTO;
use MagicPro\Messenger\Handler\MessageHandlerInterface;
use Aljerom\Albion\Application\Query\PlayerAchievementsQuery;
use Aljerom\Albion\Domain\Repository\ReadArchiveRepositoryInterface;
use Aljerom\Albion\Domain\Repository\ReadPlayerRepositoryInterface;

class PlayerAchievementsQueryHandler implements MessageHandlerInterface
{
    /**
     * @var ReadPlayerRepositoryInterface
     */
    private $playerRepo;

    /**
     * @var ReadArchiveRepositoryInterface
     */
    private $archiveRepo;

    public function __construct(
        ReadPlayerRepositoryInterface $playerRepo,
        ReadArchiveRepositoryInterface $archiveRepo
    ) {
        $this->playerRepo = $playerRepo;
        $this->archiveRepo = $archiveRepo;
    }

    /**
     * @param PlayerAchievementsQuery $query
     * @return PagedResultDTO
     */
    public function __invoke(PlayerAchievementsQuery $query): PagedResultDTO
    {
        $total = $this->playerRepo->getAchievementsTotal(['d.`guildName`' => 'OCEAN']);

        if ($query->paginator) {
            $perPage = $query->paginator->perPage;
            $offset = $query->paginator->offset();
        }
        $list = $this->playerRepo->getAchievements(['d.`guildName`' => 'OCEAN'], $perPage ?? 200, $offset ?? 0);

        $dateIn = $this->archiveRepo->getDaysInGuild('OCEAN');
        $dateIn = array_column($dateIn, null, 'name');
        foreach ($list as $playerAchievements) {
            $playerAchievements->setInGuild($dateIn[$playerAchievements->name] ?? 0);
        }

        return PagedResultDTO::fromArray(
            [
                'total' => $total,
                'page' => $query->paginator->page,
                'perPage' => $query->paginator->perPage,
                'list' => $list,
            ]
        );
    }
}
