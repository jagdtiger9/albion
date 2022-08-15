<?php

namespace albion\Application\QueryHandler;

use App\DomainModel\Dto\PagedResultDTO;
use MagicPro\Messenger\Handler\MessageHandlerInterface;
use albion\Application\Query\AwardListQuery;
use albion\Domain\Repository\ReadPlayerRepositoryInterface;

class AwardListQueryHandler implements MessageHandlerInterface
{
    /**
     * @var ReadPlayerRepositoryInterface
     */
    private $playerRepo;

    public function __construct(ReadPlayerRepositoryInterface $playerRepo)
    {
        $this->playerRepo = $playerRepo;
    }

    /**
     * @param AwardListQuery $query
     * @return PagedResultDTO
     */
    public function __invoke(AwardListQuery $query): PagedResultDTO
    {
        $total = $this->playerRepo->getAchievementsTotal(['d.`guildName`' => 'OCEAN']);

        if ($query->paginator) {
            $perPage = $query->paginator->perPage;
            $offset = $query->paginator->offset();
        }
        $list = $this->playerRepo->getAchievements(['d.`guildName`' => 'OCEAN'], $perPage ?? 200, $offset ?? 0);

        /*return array_filter(
            $list,
            static function ($item) {
                return $item->small_badge ? true : false;
            }
        );*/

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
