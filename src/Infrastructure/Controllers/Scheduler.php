<?php

namespace albion\Infrastructure\Controllers;

use Exception;
use MagicPro\Config\Config;
use albion\Services\DiscordRegistration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use MagicPro\Application\Controller;
use albion\Services\AlbionImport;

class Scheduler extends Controller
{
    public function actionImportUsers(ServerRequestInterface $request): ResponseInterface
    {
        echo '[' . date('d.m H:i:s') . '] Обновление базы Albion' . PHP_EOL;
        try {
            // OCEAN id
            (new AlbionImport())->guildImport('9ovaHeVdS0KvvGnpz-uT3w');

            (new AlbionImport())->guildImport();
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        echo '[' . date('H:i:s') . '] База Albion обновлена' . PHP_EOL;

        return $this->response;
    }

    public function actionImportOceanUsers(ServerRequestInterface $request): ResponseInterface
    {
        echo '[' . date('d.m H:i:s') . '] Обновление базы Albion для Ocean' . PHP_EOL;
        try {
            // OCEAN id
            (new AlbionImport())->guildImport('9ovaHeVdS0KvvGnpz-uT3w');
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        echo '[' . date('H:i:s') . '] База Albion обновлена для Ocean' . PHP_EOL;

        return $this->response;
    }

    public function actionDailyStat(ServerRequestInterface $request): ResponseInterface
    {
        echo '[' . date('d.m H:i:s') . '] Пересчет ежедневной статистики Albion' . PHP_EOL;
        (new AlbionImport())->dailyStat();
        echo '[' . date('H:i:s') . '] Статистика Albion обновлена' . PHP_EOL;

        return $this->response;
    }

    public function actionInitUpdatedAt(ServerRequestInterface $request): ResponseInterface
    {
        echo '[' . date('d.m H:i:s') . '] Init updated_at Albion' . PHP_EOL;
        (new AlbionImport())->initUpdatedAt();
        echo '[' . date('H:i:s') . '] Init updated_at Albion end' . PHP_EOL;

        return $this->response;
    }

    public function actionDiscordInfoUpdate(ServerRequestInterface $request): ResponseInterface
    {
        echo '[' . date('d.m H:i:s') . '] Discord info update' . PHP_EOL;
        try {
            (new DiscordRegistration(Config::get('albion')))->discordInfoUpdate();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        echo '[' . date('H:i:s') . '] Discord info update' . PHP_EOL;

        return $this->response;
    }

    public function actionDiscordList(ServerRequestInterface $request): ResponseInterface
    {
        echo '[' . date('d.m H:i:s') . '] Discord list start' . PHP_EOL;
        try {
            (new DiscordRegistration(Config::get('albion')))->discordList();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        echo '[' . date('H:i:s') . '] Discord list finish' . PHP_EOL;

        return $this->response;
    }
}
