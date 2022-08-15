<?php

namespace Aljerom\Albion\Application\CommandHandler;

use InvalidArgumentException;
use MagicPro\Messenger\Handler\MessageHandlerInterface;
use payment\Domain\Entity\ValueObject\Award;
use Aljerom\Albion\Application\Command\SetAchievementCommand;
use Aljerom\Albion\Domain\Entity\Identity\PlayerId;
use Aljerom\Albion\Domain\Entity\PlayerReward;
use Aljerom\Albion\Domain\Exception\AlbionException;
use Aljerom\Albion\Domain\Repository\PlayerRepositoryInterface;
use Aljerom\Albion\Domain\Repository\PlayerRewardRepositoryInterface;
use Aljerom\Albion\Domain\Service\PlayerPrivilege;

class SetAchievementCommandHandler implements MessageHandlerInterface
{
    /**
     * @var PlayerPrivilege
     */
    private $privilege;

    /**
     * @var PlayerRepositoryInterface
     */
    private $playerRepo;

    /**
     * @var PlayerRewardRepositoryInterface
     */
    private $playerRewardRepo;

    public function __construct(
        PlayerPrivilege                 $privilege,
        PlayerRepositoryInterface       $playerRepo,
        PlayerRewardRepositoryInterface $playerRewardRepo
    ) {
        $this->privilege = $privilege;
        $this->playerRepo = $playerRepo;
        $this->playerRewardRepo = $playerRewardRepo;
    }

    /**
     * @param SetAchievementCommand $command
     * @return bool
     * @throws AlbionException
     */
    public function __invoke(SetAchievementCommand $command): bool
    {
        // Проверили права текущего пользователя
        if (!$this->privilege->isOfficer()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 405);
        }

        if (null === $player = $this->playerRepo->findById(new PlayerId($command->id))) {
            throw new InvalidArgumentException('Игрок с указанным ID (' . $command->id . ') не найден');
        }

        if (!$player->discordId()) {
            throw new InvalidArgumentException('У игрока ' . $player->playerName()->name() . ' не привязан дискорд');
        }

        if (null === $playerReward = $this->playerRewardRepo->findByDiscordId($player->discordId())) {
            $playerReward = new PlayerReward($playerReward->discordId());
        }

        $playerReward->changeAwardStatus(new Award($command->achievement));
        if (false === $this->playerRewardRepo->save($playerReward)) {
            throw new AlbionException('Ошибка изменения статуса награды');
        }

        return true;
    }
}
