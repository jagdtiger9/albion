<?php

namespace albion\Infrastructure\Controllers;

use MagicPro\Config\Config;
use MagicPro\Http\Api\ErrorResponse;
use MagicPro\Http\Api\SuccessResponse;
use payment\Domain\Entity\ValueObject\Award;
use albion\Application\ApiResource\AwardListApi;
use albion\Application\ApiResource\CreateSnapshotApi;
use albion\Application\ApiResource\PlayerAchievementsApi;
use albion\Application\ApiResource\SetAchievementApi;
use albion\Models\EventMember;
use albion\Services\DiscordRegistration;
use albion\Services\EventMemberRegistration;
use albion\Services\EventRegistration;
use albion\Services\EventStat;
use albion\Services\GuildPlayer;
use albion\Services\MemberInGameStat;
use albion\Services\MemberPassword;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use MagicPro\Application\Controller;
use albion\Domain\Exception\AlbionException;
use albion\Models\MemberArchive;
use albion\Models\Repository\EventMemberRepository;
use albion\Models\Repository\EventRepository;
use albion\Models\Repository\GuildRepository;
use albion\Models\Repository\MemberRepository;
use albion\Services\AlbionApi;
use albion\Services\AlbionImport;
use albion\Models\Privilege\EventPrivilege;
use albion\Models\Privilege\MemberPrivilege;
use RuntimeException;
use DateTime;
use Exception;
use InvalidArgumentException;
use sessauth\Services\Authentication;
use Throwable;

class WebApi extends Controller
{
    public function actionCreateSnapshot(ServerRequestInterface $request, CreateSnapshotApi $createSnapshotApi): ResponseInterface
    {
        try {
            $command = $createSnapshotApi->getValidatedMessage($request, $this->validatedMessage);
            $this->dispatch($command);
            $apiResponse = new SuccessResponse('');
        } catch (Throwable $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }
        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionSetAchievement(ServerRequestInterface $request, SetAchievementApi $setAchievementApi): ResponseInterface
    {
        try {
            $command = $setAchievementApi->getValidatedMessage($request, $this->validatedMessage);
            $result = $this->dispatchCommand($command);
            $apiResponse = new SuccessResponse(
                'Награда "' . $command->achievement . '" ' . ($result ? 'выдана' : 'отменена')
            );
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionAwardList(ServerRequestInterface $request, AwardListApi $awardListApi): ResponseInterface
    {
        try {
            $query = $awardListApi->getValidatedMessage($request, $this->validatedMessage);
            $list = $this->dispatchQuery($query);
            $apiResponse = new SuccessResponse(['list' => $list]);
        } catch (\Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionPlayerAchievements(ServerRequestInterface $request, PlayerAchievementsApi $playerAchievementsApi): ResponseInterface
    {
        try {
            $query = $playerAchievementsApi->getValidatedMessage($request, $this->validatedMessage);
            $list = $this->dispatchQuery($query);
            $apiResponse = new SuccessResponse(
                [
                    'list' => $list,
                    'awardPoints' => Award::awardList(),
                ]
            );
        } catch (\Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionSetPrivilege(ServerRequestInterface $request, $id, $role): ResponseInterface
    {
        try {
            $repo = new MemberRepository();
            if (!$id || null === $player = $repo->getById($id)) {
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

            $player->{$role} = $player->{$role} ? 0 : 1;
            $result = $player->save();

            if (false === $result) {
                throw new AlbionException('Ошибка изменения прав');
            }

            $apiResponse = new SuccessResponse('Права "' . $role . '" ' . ($player->{$role} ? 'выданы' : 'удалены'));
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionResetPassword(ServerRequestInterface $request, $id): ResponseInterface
    {
        try {
            $memberPassword = new MemberPassword(
                Config::get('albion'),
                $request->getScheme() . '://' . $request->getUri()->getHost()
            );
            $password = $memberPassword->adminReset($id);

            $this->flash->set('newPassCode', ['id' => $id, 'password' => $password]);
            $message = [
                'msg' => 'Пароль пользователя сброшен',
                'password' => $password,
            ];
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionResetPasswordDiscord(ServerRequestInterface $request, $id, $albionName): ResponseInterface
    {
        try {
            $config = Config::get('common')->settings;
            $serverName = $config->scheme . '://' . $config->SERVER_NAME;
            $memberPassword = new MemberPassword(Config::get('albion'), $serverName);
            $accessCredentials = $memberPassword->discordReset($id, $albionName);

            $message = [
                'msg' => 'Пароль пользователя сброшен',
                'login' => $accessCredentials->login,
                'password' => $accessCredentials->password,
                'instantLoginUrl' => $serverName . $accessCredentials->instantLoginUrl,
            ];
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionAddGuild(ServerRequestInterface $request, $name): ResponseInterface
    {
        try {
            if (!$name) {
                throw new InvalidArgumentException('Не задано название гильдии');
            }

            $albionApi = new AlbionApi();
            $guild = $albionApi->search(['q' => $name])->guilds()->first();
            if (!$guild) {
                throw new InvalidArgumentException('Гильдия не найдена, "' . $name . '"');
            }

            $guildInfo = $albionApi->guildInfo($guild->Id)->first();
            $repository = new GuildRepository();
            if (false === $repository->saveGuild($guildInfo)) {
                throw new RuntimeException('Ошибка добавления гильдии');
            }
            (new AlbionImport())->guildImport($guild->Id, true);

            $message = 'Гильдия была успешно добавлена';
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionEditEvent(
        ServerRequestInterface $request,
                               $id,
                               $name,
                               $type,
                               $startDate,
                               $startTime,
                               $isMandatory,
                               $factor
    ): ResponseInterface {
        try {
            $event = (new EventRegistration())
                ->initById($id)
                ->saveEvent(
                    $name,
                    $type,
                    $startDate,
                    $startTime,
                    $isMandatory,
                    $factor
                );

            $status = 1;
            $message = 'Активность успешно ' . ($id ? 'изменена' : 'создана');
            if ($redirect_an = $request->Post('redirect_an', '')) {
                $redirectUrl = '/' . $redirect_an . '/' . $event->getField('linkHash');
            } else {
                $urlChunks = parse_url($request->getServerParams()['HTTP_REFERER'] ?? '');
                parse_str($urlChunks['query'], $queryParams);
                $query = $request->getRequestParams(['id' => $event->getField('linkHash')], $queryParams);
                $queryStr = http_build_query($query);
                $redirectUrl = $urlChunks['path'] . ($queryStr ? '?' . $queryStr : '');
            }
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $redirectUrl = true;
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect($redirectUrl));
    }

    public function actionDeleteEvent(ServerRequestInterface $request, $id): ResponseInterface
    {
        try {
            if (!$id) {
                throw new InvalidArgumentException('Не указан ID активности');
            }
            $repo = new EventRepository();
            if (null === $event = $repo->getById($id)) {
                throw new AlbionException('Активность с укзанным ID не найдена');
            }

            $userPrivilege = new EventPrivilege($event);
            $player = $userPrivilege->getMember();
            if (!$userPrivilege->isOfficer() && $event->getField('creatorName') !== $player->getField('name')) {
                throw new AlbionException('Недостаточно прав для выполнения операции', 404);
            }

            if (false === $repo->setModel($event)->delete()) {
                throw new AlbionException('Ошибка удаления активности');
            }

            $apiResponse = new SuccessResponse('Активность успешно удалена');
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionDiscordEditEvent(
        ServerRequestInterface $request,
                               $messageId,
                               $userId,
                               $name,
                               $type,
                               $time,
                               $isMandatory,
                               $factor
    ): ResponseInterface {
        try {
            $time = $time ? : time();
            $startDate = date('Y-m-d', $time);
            $startTime = date('H:i:s', $time);
            (new EventRegistration())
                ->initByDiscordId($messageId, $userId)
                ->saveEvent(
                    $name,
                    $type,
                    $startDate,
                    $startTime,
                    $isMandatory,
                    $factor
                );

            $apiResponse = new SuccessResponse('Активность успешно сохранена');
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionDiscordDeleteEvent(ServerRequestInterface $request, $messageId, $userId): ResponseInterface
    {
        try {
            //  Не удаляем активности при удалении сообщения дискорда
//            (new EventRegistration())
//                ->initByDiscordId($messageId, $userId)
//                ->deleteEvent();

            $apiResponse = new SuccessResponse('Активность успешно удалена');
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionDiscordJoinEvent(ServerRequestInterface $request, $messageId, $userId, $role): ResponseInterface
    {
        try {
            (new EventMemberRegistration())
                ->initByDiscordId($messageId, $userId)
                ->joinEvent($role);

            $message = 'Вы успешно зарегистрировались на активность, Ваша роль - ' . EventMember::ROLE_LIST[$role];
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionJoinEvent(ServerRequestInterface $request, $id, $role): ResponseInterface
    {
        try {
            (new EventMemberRegistration())
                ->initById($id)
                ->joinEvent($role);

            $message = 'Вы успешно зарегистрировались на активность, Ваша роль - ' . EventMember::ROLE_LIST[$role];
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionDiscordLeaveEvent(ServerRequestInterface $request, $messageId, $userId, $role): ResponseInterface
    {
        try {
            (new EventMemberRegistration())
                ->initByDiscordId($messageId, $userId)
                ->leaveEvent($role);

            $message = 'Вы успешно вышли из списка участников';
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionLeaveEvent(ServerRequestInterface $request, $id, $memberId): ResponseInterface
    {
        try {
            $eventMemberRegistration = new EventMemberRegistration();
            $eventMemberRegistration->initById($id);

            if ($memberId) {
                $eventMemberRegistration->kickEvent($memberId);
                $message = 'Игрок успешно удален из списка';
            } else {
                $eventMemberRegistration->leaveEvent();
                $message = 'Вы успешно вышли из списка участников';
            }

            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionSetRl(ServerRequestInterface $request, $id, $memberId): ResponseInterface
    {
        try {
            $eventRepo = new EventRepository();
            if (!$id) {
                throw new AlbionException('Не задан идентификатор активности');
            }
            if (null === $event = $eventRepo->getById($id)) {
                throw new AlbionException('Активность с укзанным ID не найдена');
            }
            $userPrivilege = new EventPrivilege($event);
            if (!$userPrivilege->isGuardian()) {
                throw new InvalidArgumentException('Недостаточно прав для управления');
            }

            if (!$memberId) {
                throw new InvalidArgumentException('Не указан ID игрока');
            }
            $player = (new MemberRepository())->getById($memberId);
            $repo = new EventMemberRepository();
            $eventMember = $repo->getEventMember($event, $player);
            if (null === $eventMember) {
                throw new AlbionException('Игрок не является участником уктивности');
            }

            if ($eventMember->getField('isRl')) {
                $eventRepo->setModel($event)->unsetRl($player, $eventMember);
                $message = 'Роль РЛ успешно снята с игрока';
            } else {
                $eventRepo->setModel($event)->setRl($player, $eventMember);
                $message = 'Игроку успешно присвоена роль РЛ';
            }

            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionRegisterEvent(ServerRequestInterface $request, $id, $name, $role): ResponseInterface
    {
        try {
            $repo = new EventRepository();
            if (!$id) {
                throw new AlbionException('Не задан идентификатор активности');
            }
            if (null === $event = $repo->getById($id)) {
                throw new AlbionException('Активность с укзанным ID не найдена');
            }

            if (null === $player = (new MemberRepository())->getBy('name', $name)) {
                throw new AlbionException('Пользователь не найден');
            }
            if ($player->getField('guildId') !== $event->getField('guildId')) {
                throw new AlbionException('Активность недоступна');
            }

            $userPrivilege = new EventPrivilege($event);
            if (!$userPrivilege->isOfficer() && $event->isRegistrationClosed()) {
                throw new AlbionException('Регистрация на активность завершена');
            }
            if (!$event->isStarted()) {
                throw new AlbionException(
                    'Регистрация на активность начнется ' .
                    (new DateTime())->setTimestamp($event->getField('started_at'))->format('d.m.y в H:i')
                );
            }

            $repo = new EventMemberRepository();
            $eventMember = $repo->getEventMember($event, $player);
            if (null !== $eventMember) {
                throw new AlbionException('Игрок ' . $name . ' уже зарегистрирован');
            }
            if (false === $repo->saveJoin($event, $player, $role)) {
                throw new AlbionException('Ошибка регистрации участника');
            }

            $message = 'Игрок ' . $name . ' успешно зарегистрирован';
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionApproveEvent(ServerRequestInterface $request, $id): ResponseInterface
    {
        try {
            $repo = new EventRepository();
            if (!$id) {
                throw new AlbionException('Не задан идентификатор активности');
            }
            if (null === $event = $repo->getById($id)) {
                throw new AlbionException('Активность с укзанным ID не найдена');
            }

            $userPrivilege = new EventPrivilege($event);
            if (!$userPrivilege->isOfficer() && !$userPrivilege->isOwner()) {
                throw new AlbionException('Недостаточно прав для выполнения операции');
            }

            if (false === $event->setApproved()->save()) {
                throw new AlbionException('Ошибка подтверждения активности');
            }

            $message = 'Активность успешно подтверждена как премиальная';
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionDisapproveEvent(ServerRequestInterface $request, $id): ResponseInterface
    {
        try {
            $repo = new EventRepository();
            if (!$id) {
                throw new AlbionException('Не задан идентификатор активности');
            }
            if (null === $event = $repo->getById($id)) {
                throw new AlbionException('Активность с укзанным ID не найдена');
            }

            $userPrivilege = new EventPrivilege($event);
            if (!$userPrivilege->isOfficer() && !$userPrivilege->isOwner()) {
                throw new AlbionException('Недостаточно прав для выполнения операции');
            }

            if (false === $event->setDisapproved()->save()) {
                throw new AlbionException('Ошибка исключения активности из премиальной статистики');
            }

            $message = 'Активность успешно исключена из премиальной статистики';
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    /**
     * @param ServerRequestInterface $request
     * @param $term
     * @param $guildId
     * @return ResponseInterface
     */
    public function actionPlayerName(ServerRequestInterface $request, $term, $guildId): ResponseInterface
    {
        $suggestions = [];
        $repo = new GuildRepository();
        if (null !== $guild = $repo->getById($guildId)) {
            $repo = new MemberRepository();
            $res = $repo->search($term . '%', $guild);
            foreach ($res as $member) {
                $suggestions[] = [
                    'value' => $member->getField('name'),
                    'id' => $member->getField('id'),
                    'name' => $member->getField('name'),
                ];
            }
        }

        return $this->response->setContent($suggestions);
    }

    public function actionBackDating(ServerRequestInterface $request, $guildId, $name, $joinDate, $joinTime): ResponseInterface
    {
        try {
            $repo = new GuildRepository();
            if (!$guildId) {
                throw new AlbionException('Не задан идентификатор гильдии');
            }
            if (null === $guild = $repo->getById($guildId)) {
                throw new AlbionException('Гильдия с укзанным ID не найдена');
            }

            if (null === $player = (new MemberRepository())->getBy('name', $name)) {
                throw new AlbionException('Игрок не найден');
            }
            if ($player->getField('guildId') !== $guildId) {
                throw new AlbionException(
                    'Игрок не является участником гильдии ' . $guild->getField('name')
                );
            }

            $userPrivilege = new MemberPrivilege();
            if (!$userPrivilege->isOfficer()) {
                throw new AlbionException('Недостаточно прав');
            }

            $historyIn = (new MemberArchive())->where('name', $player->getField('name'))
                ->where('guildId', $guildId)
                ->where('guildIn', 1)
                ->debug()
                ->get();
            if (!$historyIn) {
                $data = [
                    'id' => $player->getField('id'),
                    'name' => $player->getField('name'),
                    'guildId' => $guildId,
                    'guildName' => $guild->getField('name'),
                    'timestamp' => $joinDate . 'T00:10:11.181100Z',
                    'lastActive_at' => $joinDate . ' ' . $joinTime,
                    'guildIn' => 1,
                    'guildOut' => '',
                    'updated_at' => $joinDate . ' ' . $joinTime,
                ];
                if (!(new MemberArchive())->fill($data)->save()) {
                    throw new AlbionException('Ошибка записи данных в БД');
                }
            }

            $message = 'Информация о присоединении игрока к гильдии добавлена';
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionDiscordRegister(ServerRequestInterface $request, $discordId, $discordName, $albionName): ResponseInterface
    {
        try {
            if (!$discordId || !$discordName) {
                throw new AlbionException('Не авторизованное обращение', 11);
            }
            if (!$albionName) {
                throw new AlbionException('Не указано имя игрока в Альбионе', 12);
            }

            $discordRegistration = new DiscordRegistration(Config::get('albion'));
            $loginHash = $discordRegistration->registerUser($discordId, $discordName, $albionName);

            $message = [
                'message' => 'Запрос на регистрацию успешно отправлен',
                'moderateLink' => '/management/discord',
                'moderateAuthLink' => $discordRegistration->getModerateLink(
                    $discordId,
                    $loginHash,
                    '/management/discord'
                ),
            ];
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionDiscordModeratorLogin(ServerRequestInterface $request, $loginHash, $redirectUri, $discordModeratorId): ResponseInterface
    {
        $discordRegistration = new DiscordRegistration(Config::get('albion'));
        if (null !== $user = $discordRegistration->getHashLoginUser($loginHash, $discordModeratorId)) {
            $auth = new Authentication($user);
            $auth->setAutoLogin($this->response);
            $auth->initSession();
        }

        return $this->response->redirect(urldecode($redirectUri));
    }

    public function actionDiscordConfirm(ServerRequestInterface $request, $discordId, $albionName): ResponseInterface
    {
        try {
            (new DiscordRegistration(Config::get('albion')))->confirm($discordId, $albionName);
            $apiResponse = new SuccessResponse('Регистрация успешно подтверждена');
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionDiscordReject(ServerRequestInterface $request, $discordId, $albionName): ResponseInterface
    {
        try {
            (new DiscordRegistration(Config::get('albion')))->reject($discordId, $albionName);
            $apiResponse = new SuccessResponse('Регистрация успешно отклонена');
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionDiscordReset(ServerRequestInterface $request, $discordId, $albionName): ResponseInterface
    {
        try {
            (new DiscordRegistration(Config::get('albion')))->reset($discordId, $albionName);
            $apiResponse = new SuccessResponse('Регистрация успешно удалена');
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionLinkDiscordAccount(ServerRequestInterface $request, $albionName, $discordId, $isTwink): ResponseInterface
    {
        try {
            if (!$albionName) {
                throw new AlbionException('Не указано имя игрока в Альбионе', 12);
            }
            if (!$discordId) {
                throw new AlbionException('Не задан discord id', 11);
            }

            $discordRegistration = new DiscordRegistration(Config::get('albion'));
            $member = $discordRegistration->linkDiscordAccount($discordId, $albionName, $isTwink);

            $message = [
                'msg' => 'Учетная запись discord успешно привязана',
                'account' => $member,
            ];
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionCheckGoneAccount(ServerRequestInterface $request, $name): ResponseInterface
    {
        try {
            if (!$name) {
                throw new AlbionException('Не указано имя игрока в Альбионе', 12);
            }

            $discordRegistration = new DiscordRegistration(Config::get('albion'));
            $discordRegistration->linkDiscordAccount(0, $name, 0);

            $message = [
                'msg' => 'Данные успешно обновлены'
            ];
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionKillsUpdate(ServerRequestInterface $request, $albionName, $killCount): ResponseInterface
    {
        try {
            $gameStat = new MemberInGameStat();
            $member = $gameStat->setKills($albionName, $killCount);

            $message = [
                'msg' => 'Данные успешно обновлены',
                'account' => $member,
            ];
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionDonationUpdate(ServerRequestInterface $request, $albionName, $donation): ResponseInterface
    {
        try {
            $gameStat = new MemberInGameStat();
            $member = $gameStat->setDonation($albionName, $donation);

            $message = [
                'msg' => 'Данные успешно обновлены',
                'account' => $member,
            ];
            $apiResponse = new SuccessResponse($message);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionPlayerList(ServerRequestInterface $request, $guildName): ResponseInterface
    {
        try {
            $params = $request->getQueryParams();
            $guildPlayer = new GuildPlayer($guildName);
            $data = $guildPlayer->setOrder($params['sort'] ?? 'name', $params['order'] ?? 'asc')
                ->setPage($params['page'] ?? 0, $params['perPage'] ?? 300)
                ->setRange($params['from'] ?? 0, $params['to'] ?? 0)
                ->get();

            $apiResponse = new SuccessResponse($data);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }

    public function actionEventStat(ServerRequestInterface $request, $from): ResponseInterface
    {
        try {
            $dateFrom = (new DateTime())->sub(new \DateInterval('P14D'))->getTimestamp();
            $eventStat = new EventStat();
            $data = $eventStat->getList($dateFrom);

            $apiResponse = new SuccessResponse($data);
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse);
    }
}
