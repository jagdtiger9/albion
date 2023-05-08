<?php

namespace Aljerom\Albion\Application\ApiResource;

use MagicPro\Messenger\Validation\ValidatedMessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Api\AbstractApiResource;
use Aljerom\Albion\Application\Query\AwardListQuery;

class AwardListApi extends AbstractApiResource
{
    public function getResourceDescription(): string
    {
        return <<<STR
Список игроков, представленных к награждению
STR;
    }

    public function getValidatedMessage(ServerRequestInterface $request, ValidatedMessageInterface $validatedMessage): AwardListQuery
    {
        return $validatedMessage->fromRequest($this->inputParam(), $request);
    }

    public function inputParam(): string
    {
        return AwardListQuery::class;
    }

    public function outputParam(): string
    {
        return 'Список';
    }

    /**
     * @inheritDoc
     */
    public function isAuthorizationRequired(): bool
    {
        return true;
    }
}
