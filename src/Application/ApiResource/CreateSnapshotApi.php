<?php

namespace Aljerom\Albion\Application\ApiResource;

use App\Api\AbstractApiResource;
use MagicPro\Messenger\Validation\ValidatedMessageInterface;
use Aljerom\Albion\Application\Command\CreateSnapshotCommand;
use Psr\Http\Message\ServerRequestInterface;

class CreateSnapshotApi extends AbstractApiResource
{
    public function getResourceDescription(): string
    {
        return <<<STR
Снимок и фиксация текущего состояния с баллами игроков 
STR;
    }

    public function getValidatedMessage(ServerRequestInterface $request, ValidatedMessageInterface $validatedMessage): CreateSnapshotCommand
    {
        return $validatedMessage->fromRequest($this->inputParam(), $request);
    }

    public function inputParam(): string
    {
        return CreateSnapshotCommand::class;
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
