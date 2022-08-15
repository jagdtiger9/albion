<?php

namespace albion\Application\Command;

use MagicPro\Messenger\Message\CommandInterface;
use MagicPro\Messenger\Validation\MessageValidationInterface;
use MagicPro\Messenger\Validation\MessageValidationTrait;
use MagicPro\DomainModel\Dto\SimpleDto;
use payment\Domain\Entity\ValueObject\Award;
use albion\Application\CommandHandler\SetAchievementCommandHandler;

class SetAchievementCommand extends SimpleDto implements CommandInterface, MessageValidationInterface
{
    use MessageValidationTrait;

    /**
     * Идентификатор игрока
     * @var
     */
    public $id;

    /**
     * Тип награды
     * @var
     */
    public $achievement;

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'id' => 'required|string',
            'achievement' => 'required|in:' . Award::awardList(true),
        ];
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getHandlerClassName(): string
    {
        return SetAchievementCommandHandler::class;
    }
}
