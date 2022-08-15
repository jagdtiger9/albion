<?php

namespace Aljerom\Albion\Services;

use InvalidArgumentException;
use MagicPro\Config\Config;
use Aljerom\Albion\Domain\Exception\AlbionException;
use Aljerom\Albion\Models\Dto\AccessCredentials;
use Aljerom\Albion\Models\Member;
use Aljerom\Albion\Models\Privilege\MemberPrivilege;
use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;
use RestCord\DiscordClient;
use RuntimeException;
use sessauth\Domain\Models\User;
use sessauth\Services\Authentication;
use sessauth\Services\UserMod\Create;

class MemberPassword
{
    /**
     * Время на логин по ссылке
     */
    public const HASH_LOGIN_TTL = 300;

    private $config;

    private $serverName;

    public function __construct(Config $config, string $serverName)
    {
        $this->config = $config->settings;
        $this->serverName = $serverName;
    }

    /**
     * Сброс пароля члену гильдии админом или офицером ги
     *
     * @param $id
     * @return string
     * @throws AlbionException
     */
    public function adminReset($id): string
    {
        if (!$id) {
            throw new InvalidArgumentException('Не указан ID игрока');
        }
        $repo = new MemberRepository();
        if (null === $player = $repo->getById($id)) {
            throw new InvalidArgumentException('Игрок с указанным ID (' . $id . ') не найден');
        }
        $repo = new GuildRepository();
        if (null === $repo->getById($player->getField('guildId'))) {
            throw new InvalidArgumentException('Гильдия с указанным ID (' . $player->getField('guildId') . ') не найдена');
        }

        $userPrivilege = new MemberPrivilege();
        if (!$userPrivilege->isOfficer()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 405);
        }

        $accessCredentials = $this->resetPassword($player);

        if ($player->discordId) {
            $discord = new DiscordClient(['token' => $this->config->token]);
            //$discord->gateway->getGatewayBot()
            $userDM = $discord->user->createDm(['recipient_id' => (int)$player->discordId]);
            $message = 'Ваш логин: ' . $player->name . PHP_EOL .
                'Ваш пароль: ' . $accessCredentials->password . PHP_EOL . PHP_EOL .
                '[Доступ без пароля](' . $this->serverName . $accessCredentials->instantLoginUrl . ')' . PHP_EOL .
                '*ссылка действительна в течение ' . (self::HASH_LOGIN_TTL / 60) . ' минут*';
            $discord->channel->createMessage(
                [
                    'channel.id' => $userDM->id,
                    'content' => '',
                    'embed' => [
                        'title' => 'Доступ к **albion.gudilap.ru**',
                        'description' => $message,
                        'url' => $this->serverName,
                    ]
                ]
            );
        }

        return $accessCredentials->password;
    }

    /**
     * @param $id
     * @param $albionName
     * @return AccessCredentials
     * @throws AlbionException
     */
    public function discordReset($id, $albionName): AccessCredentials
    {
        if (!$id) {
            throw new InvalidArgumentException('Не указан ID дискорда', 12);
        }

        if ($albionName) {
            $repo = new MemberRepository();
            if (null === $player = $repo->getBy('name', $albionName)) {
                throw new InvalidArgumentException('Игрок (' . $albionName . ') не найден', 11);
            }
            if ((int)$player->discordId !== $id) {
                throw new InvalidArgumentException(
                    'Игровой ник ' . $albionName . ' не привязан к Вашему дискорду', 13
                );
            }
        } else {
            $player = (new MemberRepository())->getMainByDiscord($id);
            if (null === $player) {
                throw new AlbionException('Нет привязанных игровых аккаунтов', 14);
            }
        }

        return $this->resetPassword($player);
    }

    /**
     * Установка нового пароля для члена гильдии
     *
     * @param Member $player
     * @return AccessCredentials
     */
    private function resetPassword(Member $player): AccessCredentials
    {
        $login = $player->getField('name');
        if (null === $user = User::where('login', $login)->first()) {
            $params = [
                'login' => $login,
                'active' => 1,
                'confirmHash' => '',
            ];
            $modUser = new Create();
            $modUser->autoPass()->noCheckEmail()->create($params);
            $password = $modUser->plainPassword();
        } else {
            $password = $user->requestNewPass();
            if (false === $user->save()) {
                throw new RuntimeException($user->lastError());
            }
        }
        $player->setActive();
        $instantLoginUrl = (new Authentication($user))->getInstantLoginUrl('', self::HASH_LOGIN_TTL);

        return new AccessCredentials($player->getField('name'), $password, $instantLoginUrl);
    }
}
