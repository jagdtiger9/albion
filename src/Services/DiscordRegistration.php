<?php

namespace Aljerom\Albion\Services;

use Exception;
use GuzzleHttp\Command\Exception\CommandClientException;
use InvalidArgumentException;
use MagicPro\Config\Config;
use Aljerom\Albion\Domain\Exception\AlbionException;
use Aljerom\Albion\Models\Member;
use Aljerom\Albion\Models\MemberRoles;
use Aljerom\Albion\Models\Privilege\MemberPrivilege;
use Aljerom\Albion\Models\Repository\DiscordRegistrationRepository;
use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\LoginHashRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;
use RestCord\DiscordClient;
use sessauth\Domain\Models\Repository\UserRepository;
use sessauth\Domain\Models\User;

class DiscordRegistration
{
    private const MODERATE_URI = '/api/albion/discordModeratorLogin/%s/%s';

    private $guildName;

    private $config;

    public function __construct(Config $config)
    {
        $this->guildName = $config->guildName;
        $this->config = $config->settings;
    }

    /**
     * @param $discordId
     * @param $discordName
     * @param $albionName
     * @return string|null
     * @throws AlbionException
     */
    public function registerUser($discordId, $discordName, $albionName): ?string
    {
        $isTwink = false;
        if ($albionName[0] === '+') {
            $isTwink = true;
            $albionName = substr($albionName, 1);
        }
        /*$albionApi = new AlbionApi();
        $data['apiUserList'] = $albionApi->setReturnArray()
            ->search(['q' => $albionName])
            ->players()
            ->get();*/
        if (null === $member = (new MemberRepository())->getBy('name', $albionName)) {
            throw new AlbionException('Игрок ' . $albionName . ' не найден в БД albion.gudilap.ru');
        }
        if ($member->guildName !== $this->guildName) {
            throw new AlbionException(
                'Игрок ' . $albionName . ' не является членом гильдии ' . $this->guildName . PHP_EOL .
                'Из-за проблем с AlbionAPI добавление пользователей в ги происходит с задержкой' . PHP_EOL .
                'Повторите, пожалуйста, запрос позже'
            );
        }

        $repo = new DiscordRegistrationRepository();
        $discordRegistration = $repo->findOrInit([$discordId, $albionName]);

        if ($discordRegistration->isConfirmed()) {
            throw new AlbionException(
                'Вы уже зарегистрировали ник ' . $discordRegistration->albionName
            );
        }

        if (!$discordRegistration->register($discordName, $member, $isTwink)->save()) {
            throw new AlbionException('Ошибка регистрации');
        }

        $loginHash = (new LoginHashRepository())->findOrInit($discordId);
        $loginHash->updateHash()->save();

        return $loginHash->getHash();
    }

    public function getModerateLink($discordId, $loginHash, $redirectUrl): string
    {
        return sprintf(
            self::MODERATE_URI,
            $discordId . '.' . $loginHash,
            urlencode($redirectUrl)
        );
    }

    public function getHashLoginUser($hash, $discordModeratorId): ?User
    {
        if (null === $member = (new MemberRepository())->getDiscordOfficer($discordModeratorId)) {
            return null;
        }

        [$salt, $hash] = explode('.', $hash);
        $loginHash = (new LoginHashRepository())->getById($salt);
        if (!$loginHash || $hash !== $loginHash->getHash()) {
            return null;
        }

        return (new UserRepository())->getByLogin($member->name);
    }

    /**
     * @param $discordId
     * @param $albionName
     * @throws AlbionException
     */
    public function confirm($discordId, $albionName): void
    {
        $repo = new GuildRepository();
        if (null === $guild = $repo->getBy('name', $this->guildName)) {
            throw new InvalidArgumentException('Гильдия с указанным ID (' . $this->guildName . ') не найдена');
        }
        $userPrivilege = new MemberPrivilege();
        if (!$userPrivilege->isOfficer()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 12);
        }

        if (!$discordId) {
            throw new AlbionException('Не указан ID дискорд пользователя', 11);
        }
        if (null === $discordRegistration = (new DiscordRegistrationRepository())->getById([$discordId, $albionName])) {
            throw new AlbionException('Регистрация не найдена', 13);
        }

        if (!$discordRegistration->confirm($userPrivilege->getUser())->save()) {
            throw new AlbionException('Ошибка подтверждения регистрации');
        }

        $member = (new MemberRepository())->findOrInit($discordRegistration->albionId);
        if (!$member->addDiscordRegistration($discordRegistration, $guild)->save()) {
            throw new AlbionException('Ошибка подтверждения регистрации');
        }
    }

    /**
     * @param $discordId
     * @param $albionName
     * @throws AlbionException
     */
    public function reject($discordId, $albionName): void
    {
        $repo = new GuildRepository();
        if (null === $guild = $repo->getBy('name', $this->guildName)) {
            throw new InvalidArgumentException('Гильдия с указанным ID (' . $this->guildName . ') не найдена');
        }
        $userPrivilege = new MemberPrivilege();
        if (!$userPrivilege->isOfficer()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 12);
        }

        if (!$discordId) {
            throw new AlbionException('Не указан ID дискорд пользователя', 11);
        }
        if (null === $discordRegistration = (new DiscordRegistrationRepository())->getById([$discordId, $albionName])) {
            throw new AlbionException('Регистрация не найдена', 13);
        }

        if (!$discordRegistration->delete()) {
            throw new AlbionException('Ошибка отклонения регистрации');
        }
    }

    /**
     * @param $discordId
     * @param $albionName
     * @throws AlbionException
     */
    public function reset($discordId, $albionName): void
    {
        $repo = new GuildRepository();
        if (null === $guild = $repo->getBy('name', $this->guildName)) {
            throw new InvalidArgumentException('Гильдия с указанным ID (' . $this->guildName . ') не найдена');
        }
        $userPrivilege = new MemberPrivilege();
        if (!$userPrivilege->isOfficer()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 12);
        }

        if (!$discordId) {
            throw new AlbionException('Не указан ID дискорд пользователя', 11);
        }
        if (null === $discordRegistration = (new DiscordRegistrationRepository())->getById([$discordId, $albionName])) {
            throw new AlbionException('Регистрация не найдена', 13);
        }

        $albionId = $discordRegistration->albionId;
        if (!$discordRegistration->delete()) {
            throw new AlbionException('Ошибка удаления регистрации');
        }

        if (null !== $member = (new MemberRepository())->getById($albionId)) {
            if (!$member->resetDiscord()->save()) {
                throw new AlbionException('Ошибка удаления регистрации');
            }
        }
    }

    /**
     * 1. 504 timeout регулярно
     *      если ошибка, кидаем сообщение пользова телю, к рег сообщению аттачим картинку,
     *      при клике на которую повторно пытаемся зарегистрировать
     * 2. Проблема дублирующихся ников в игре, например:
     *      http://gameinfo.albiononline.com/api/gameinfo/search?q=JETracktor
     *
     * @param $name
     * @return array|object
     */
    private function checkAlbionBase($name)
    {
        try {
            $albionApi = new AlbionApi();
            $players = $albionApi->setReturnArray()
                ->search(['q' => $name])
                ->players()
                ->get();
            $result = array_filter(
                $players,
                static function ($item) use ($name) {
                    return $item['Name'] === $name;
                }
            );

            return $result;
        } catch (Exception $e) {
            $e->getCode();
            $e->getMessage();
        }
    }

    /**
     * @param $discordId
     * @param $albionName
     * @param $isTwink
     * @return Member|null
     * @throws AlbionException
     */
    public function linkDiscordAccount($discordId, $albionName, $isTwink): ?Member
    {
        if (!is_numeric($isTwink)) {
            throw new InvalidArgumentException('Параметр isTwink может принимать значения: 0,1');
        }
        $isTwink = (int)$isTwink;
        $repo = new GuildRepository();
        if (null === $repo->getBy('name', $this->guildName)) {
            throw new InvalidArgumentException('Гильдия с указанным ID (' . $this->guildName . ') не найдена');
        }
        $userPrivilege = new MemberPrivilege();
        if (!$userPrivilege->isOfficer()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 12);
        }

        if (null === $member = (new MemberRepository())->getBy('name', $albionName)) {
            throw new AlbionException('Игрок ' . $albionName . ' не найден в БД albion.gudilap.ru');
        }
        /*if ($member->guildName !== $this->guildName) {
            throw new AlbionException(
                'Игрок ' . $albionName . ' не является членом гильдии ' . $this->guildName
            );
        }*/
        $discordId = $discordId ? : $member->discordId;

        $discord = new DiscordClient(['token' => $this->config->token]);
        try {
            $guildMember = $discord->guild->getGuildMember(
                [
                    'guild.id' => (int)$this->config->guildId,
                    'user.id' => (int)$discordId,
                ]
            );

            $memberRoles = (new MemberRoles($this->config))->getRoleList($guildMember->roles);

            if (!$member->addDiscordInfo($discordId, $guildMember->user->username, $isTwink, $memberRoles)->save()) {
                throw new AlbionException('Ошибка подключения discord учетки');
            }
        } catch (CommandClientException $e) {
            // Пользователь вышел из дискорда
            if ($e->getResponse()->getStatusCode() === 404) {
                if (!$member->resetDiscord()->save()) {
                    throw new AlbionException('Ошибка подключения discord учетки');
                }
            }
        }

        return $member;
    }

    /**
     * @throws AlbionException
     */
    public function discordInfoUpdate(): void
    {
        if (null === $guild = (new GuildRepository())->getBy('name', $this->guildName)) {
            throw new InvalidArgumentException('Гильдия с указанным ID (' . $this->guildName . ') не найдена');
        }
        $members = (new MemberRepository())->getByGuild($guild);
        $memberRoles = new MemberRoles($this->config);
        $discord = new DiscordClient(['token' => $this->config->token]);

        foreach ($members as $member) {
            if (!$member->discordId) {
                continue;
            }

            $guildMember = $discord->guild->getGuildMember(
                [
                    'guild.id' => (int)$this->config->guildId,
                    'user.id' => (int)$member->discordId
                ]
            );

            $saveResult = $member->addDiscordInfo(
                $member->discordId,
                $guildMember->user->username,
                null,
                $memberRoles->getRoleList($guildMember->roles)
            )->save();
            if (!$saveResult) {
                throw new AlbionException('Ошибка обновления данных discord учетки');
            }

            echo $member->name . PHP_EOL;
            sleep(3);
        }
    }

    /**
     * @throws AlbionException
     */
    public function discordList(): void
    {
        $discord = new DiscordClient(['token' => $this->config->token]);

        $guildMember = $discord->guild->listGuildMembers(
            [
                'guild.id' => (int)$this->config->guildId,
            ]
        );

        var_dump($guildMember);
    }
}
