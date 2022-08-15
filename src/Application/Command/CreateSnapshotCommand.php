<?php

namespace albion\Application\Command;

use MagicPro\Messenger\Message\CommandInterface;
use MagicPro\Messenger\Validation\MessageValidationInterface;
use MagicPro\Messenger\Validation\MessageValidationTrait;
use MagicPro\DomainModel\Dto\SimpleDto;
use albion\Application\CommandHandler\CreateSnapshotCommandHandler;

class CreateSnapshotCommand extends SimpleDto implements CommandInterface, MessageValidationInterface
{
    use MessageValidationTrait;

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getHandlerClassName(): string
    {
        return CreateSnapshotCommandHandler::class;
    }
}
