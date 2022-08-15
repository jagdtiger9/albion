<?php

namespace albion\Application\ApiResource;

use App\Api\AbstractApiResource;
use MagicPro\Messenger\Validation\ValidatedMessageInterface;
use albion\Application\Command\SetAchievementCommand;
use Psr\Http\Message\ServerRequestInterface;

class SetAchievementApi extends AbstractApiResource
{
    public function getResourceDescription(): string
    {
        return <<<STR
Отметка о награде, выдаваемой игроку
STR;
    }

    public function getValidatedMessage(ServerRequestInterface $request, ValidatedMessageInterface $validatedMessage): SetAchievementCommand
    {
        return $validatedMessage->fromRequest($this->inputParam(), $request);
    }

    public function inputParam(): string
    {
        return SetAchievementCommand::class;
    }

    public function outputParam(): string
    {
        return 'Пустая строка, используем API статус для получения результата';
    }

    /**
     * @inheritDoc
     */
    public function isAuthRequired(): bool
    {
        return true;
    }
}
