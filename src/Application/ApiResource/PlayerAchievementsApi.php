<?php

namespace albion\Application\ApiResource;

use MagicPro\Messenger\Validation\ValidatedMessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Api\AbstractApiResource;
use albion\Application\Query\PlayerAchievementsQuery;

class PlayerAchievementsApi extends AbstractApiResource
{
    public function getResourceDescription(): string
    {
        return <<<STR
Список игроков для награждения баллами
STR;
    }

    public function getValidatedMessage(ServerRequestInterface $request, ValidatedMessageInterface $validatedMessage): PlayerAchievementsQuery
    {
        return $validatedMessage->fromRequest($this->inputParam(), $request);
    }

    public function inputParam(): string
    {
        return PlayerAchievementsQuery::class;
    }

    public function outputParam(): string
    {
        return 'Список';
    }
}
