<?php

namespace Aljerom\Albion\Infrastructure\Controllers;

use MagicPro\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use MagicPro\Application\Controller;
use RestCord\DiscordClient;

class MainAdmin extends Controller
{
    public function actionIndex(ServerRequestInterface $request): ResponseInterface
    {
        $config = Config::get('albion');
        $discord = new DiscordClient(['token' => $config->token]); // Token is required
        $guildMember = $discord->guild->getGuildMember(['guild.id' => (int)$config->guildId, 'user.id' => 215036376654020608]);
        //var_dump($discord->guild->listGuildMembers(['guild.id' => 119764881800036352, 'limit' => 10]));
        //var_dump($discord->guild->getGuild(['guild.id' => 119764881800036352]));

        return $this->response->setContent($guildMember->roles);
    }
}
