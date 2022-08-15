<?php

use albion\Application\ApiResource\AwardListApi;
use albion\Application\ApiResource\CreateSnapshotApi;
use albion\Application\ApiResource\PlayerAchievementsApi;
use albion\Application\ApiResource\SetAchievementApi;
use albion\Infrastructure\Controllers\WebApi;
use albion\Models\Event;
use albion\Models\Member;

return [
    'POST:createSnapshot' => [
        'controller' => [WebApi::class, 'createSnapshot'],
        'apiResource' => CreateSnapshotApi::class,
    ],
    'GET:setAchievement' => [
        'controller' => [WebApi::class, 'setAchievement'],
        'apiResource' => SetAchievementApi::class,
    ],
    'GET:awardList' => [
        'controller' => [WebApi::class, 'awardList'],
        'apiResource' => AwardListApi::class,
    ],
    'GET:playerAchievements' => [
        'controller' => [WebApi::class, 'playerAchievements'],
        'apiResource' => PlayerAchievementsApi::class,
    ],
    /**
     * Назначение пользователю определенной роли
     *
     * @return Api,Redirect
     */
    'GET:setPrivilege' => [
        'controller' => [WebApi::class, 'setPrivilege'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Идентификатор пользователя'
            ],
            'role' => [
                'value' => 'rl',
                'filter' => Member::AUTH_LIST,
                'comment' => 'Выдаваемая роль'
            ],
        ],
        'local' => true,
    ],
    /**
     * Сброс пароля для указанного игрока
     *
     * @return Api,Redirect
     */
    'GET:resetPassword' => [
        'controller' => [WebApi::class, 'resetPassword'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Идентификатор пользователя'
            ],
        ],
        'local' => true,
    ],
    /**
     * Сброс пароля через запрос из discord
     *
     * @return Api,Redirect
     */
    'GET:resetPasswordDiscord' => [
        'controller' => [WebApi::class, 'resetPasswordDiscord'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Идентификатор пользователя в discord'
            ],
            'albionName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в albion'
            ],
        ],
    ],
    /**
     * Добавление гильдии
     *
     * @return Api,Redirect
     */
    'POST:addGuild' => [
        'controller' => [WebApi::class, 'addGuild'],
        'params' => [
            'name' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Название гильдии'
            ],
        ],
    ],
    /**
     * Добавление-редактирование активности
     *
     * @return Api,Redirect
     */
    'POST:editEvent' => [
        'controller' => [WebApi::class, 'editEvent'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'ID активности для редактирования'
            ],
            'name' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Название активности'
            ],
            'type' => [
                'value' => Event::TYPE_ZVZ,
                'filter' => array_keys(Event::TYPE_LIST),
                'comment' => 'Тип активности: ' . implode(',', array_keys(Event::TYPE_LIST)),
            ],
            'startDate' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Дата начала события'
            ],
            'startTime' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Время начала события'
            ],
            'isMandatory' => [
                'value' => 0,
                'filter' => [0, 1],
                'comment' => 'Признак, обязательное событие'
            ],
            'factor' => [
                'value' => 1,
                'filter' => FILTER_VALIDATE_FLOAT,
                'comment' => 'Коеффициент важности активности'
            ],
        ],
        'local' => true,
    ],
    /**
     * Удаление активности
     *
     * @return Api,Redirect
     */
    'GET:deleteEvent' => [
        'controller' => [WebApi::class, 'deleteEvent'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'ID удаляемой активности'
            ],
        ],
    ],
    /**
     * Присоединение к списку участников активности
     *
     * @return Api,Redirect
     */
    'GET:joinEvent' => [
        'controller' => [WebApi::class, 'joinEvent'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Идентификатор события'
            ],
            'role' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Роль участника события'
            ],
        ],
    ],
    /**
     * Выход из списка участников активности
     *
     * @return Api,Redirect
     */
    'GET:leaveEvent' => [
        'controller' => [WebApi::class, 'leaveEvent'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Идентификатор события'
            ],
            'memberId' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Идентификатор участника, если не задан - текущий пользователь',
            ],
        ],
    ],
    /**
     * Назначение (разжалование из) РЛ
     *
     * @return Api,Redirect
     */
    'GET:setRl' => [
        'controller' => [WebApi::class, 'setRl'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Идентификатор события'
            ],
            'memberId' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Идентификатор участника',
            ],
        ],
    ],
    /**
     * Регистрация анонимного пользователя на активность
     *
     * @return Api, Redirect
     */
    'POST:registerEvent' => [
        'controller' => [WebApi::class, 'registerEvent'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Идентификатор события'
            ],
            'name' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя участника события'
            ],
            'role' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Роль участника события'
            ],
        ],
    ],
    /**
     * Подтверждение, что активность состоялась согласно правил и будет учтена
     *
     * @return Api, Redirect
     */
    'GET:approveEvent' => [
        'controller' => [WebApi::class, 'approveEvent'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Идентификатор события'
            ],
        ],
        'local' => true,
    ],
    /**
     * Отметка, что активность не состоялась согласно правил и не будет учтена
     *
     * @return Api, Redirect
     */
    'GET:disapproveEvent' => [
        'controller' => [WebApi::class, 'disapproveEvent'],
        'params' => [
            'id' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Идентификатор события'
            ],
        ],
        'local' => true,
    ],
    /**
     * Автокомплит логинов пользователей
     *
     * @return Json
     */
    'GET:playerName' => [
        'controller' => [WebApi::class, 'playerName'],
        'params' => [
            'term' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Строка для автокомплита'
            ],
            'guildId' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Идентификатор гильдии'
            ],
        ]
    ],
    'POST:backDating' => [
        'controller' => [WebApi::class, 'backDating'],
        'params' => [
            'guildId' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Идентификатор гильдии'
            ],
            'name' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя игрока'
            ],
            'joinDate' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Дата',
            ],
            'joinTime' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Дата',
            ],
        ],
    ],
    /**
     * Регистрация discord пользователя, привязка аккаунта
     *
     * @return Api,Redirect
     */
    'POST:discordRegister' => [
        'controller' => [WebApi::class, 'discordRegister'],
        'params' => [
            'discordId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID пользователя в discord'
            ],
            'discordName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в discord'
            ],
            'albionName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в albion'
            ],
        ],
    ],
    /**
     * Вход модератора учетных записей discord
     * Обязательно должен быть офицером
     *
     * @return Api,Redirect
     */
    'GET:discordModeratorLogin' => [
        'controller' => [WebApi::class, 'discordModeratorLogin'],
        'params' => [
            'loginHash' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Hash для авторизации без пароля'
            ],
            'redirectUri' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Редирект в случае успешной авторизации'
            ],
            'discordModeratorId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID пользователя в discord'
            ],
        ],
    ],
    /**
     * Подтверждение регистрации discord пользователя
     *
     * @return Api,Redirect
     */
    'GET:discordConfirm' => [
        'controller' => [WebApi::class, 'discordConfirm'],
        'params' => [
            'discordId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID пользователя в discord'
            ],
            'albionName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в albion'
            ],
        ],
    ],
    /**
     * Отклонение регистрации discord пользователя
     *
     * @return Api,Redirect
     */
    'GET:discordReject' => [
        'controller' => [WebApi::class, 'discordReject'],
        'params' => [
            'discordId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID пользователя в discord'
            ],
            'albionName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в albion'
            ],
        ],
    ],
    /**
     * Удаление регистрации discord пользователя
     *
     * @return Api,Redirect
     */
    'GET:discordReset' => [
        'controller' => [WebApi::class, 'discordReset'],
        'params' => [
            'discordId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID пользователя в discord'
            ],
            'albionName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в albion'
            ],
        ],
    ],
    /**
     * Добавление-редактирование активности
     *
     * @return Api,Redirect
     */
    'POST:discordEditEvent' => [
        'controller' => [WebApi::class, 'discordEditEvent'],
        'params' => [
            'messageId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID сообщения дискорда, привязка к активности'
            ],
            'userId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID пользователя дискорда'
            ],
            'name' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Название активности'
            ],
            'type' => [
                'value' => Event::TYPE_ZVZ,
                'filter' => array_keys(Event::TYPE_LIST),
                'comment' => 'Тип активности: ' . implode(',', array_keys(Event::TYPE_LIST)),
            ],
            'time' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Время начала события'
            ],
            'isMandatory' => [
                'value' => 0,
                'filter' => [0, 1],
                'comment' => 'Признак, обязательное событие'
            ],
            'factor' => [
                'value' => 1,
                'filter' => FILTER_VALIDATE_FLOAT,
                'comment' => 'Коеффициент важности активности'
            ],
        ],
    ],
    /**
     * Удаление активности
     *
     * @return Api,Redirect
     */
    'POST:discordDeleteEvent' => [
        'controller' => [WebApi::class, 'discordDeleteEvent'],
        'params' => [
            'messageId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID сообщения дискорда, привязан к активности'
            ],
            'userId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID сообщения дискорда, привязан к активности'
            ],
        ],
    ],
    /**
     * Присоединение к списку участников активности
     *
     * @return Api,Redirect
     */
    'GET:discordJoinEvent' => [
        'controller' => [WebApi::class, 'discordJoinEvent'],
        'params' => [
            'messageId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID сообщения дискорда, привязан к активности'
            ],
            'userId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID пользователя дискорда'
            ],
            'role' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Роль участника события'
            ],
        ],
    ],
    /**
     * Присоединение к списку участников активности
     *
     * @return Api,Redirect
     */
    'GET:discordLeaveEvent' => [
        'controller' => [WebApi::class, 'discordLeaveEvent'],
        'params' => [
            'messageId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID сообщения дискорда, привязан к активности'
            ],
            'userId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID пользователя дискорда'
            ],
            'role' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Роль участника события'
            ],
        ],
    ],
    /**
     * Список игроков
     *
     * @return Api,Redirect
     */
    'GET:playerList' => [
        'controller' => [WebApi::class, 'playerList'],
        'params' => [
            'guildName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Название гильдии'
            ],
        ],
    ],
    /**
     * Подключение аккаунта discord для игрока гильдии
     *
     * @return Api,Redirect
     */
    'POST:linkDiscordAccount' => [
        'controller' => [WebApi::class, 'linkDiscordAccount'],
        'params' => [
            'albionName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в albion'
            ],
            'discordId' => [
                'value' => '',
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'ID пользователя в discord'
            ],
            'isTwink' => [
                'value' => 0,
                'filter' => [0, 1],
                'comment' => 'Игровой твинк, признак'
            ],
        ],
    ],
    /**
     * Проверка-синхронизация ролей пользователя, вышедшего из гильдии
     *
     * @return Api,Redirect
     */
    'GET:checkGoneAccount' => [
        'controller' => [WebApi::class, 'checkGoneAccount'],
        'params' => [
            'name' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в albion'
            ],
        ],
    ],
    /**
     * Обновление кол-ва убийств игроком
     *
     * @return Api,Redirect
     */
    'POST:killsUpdate' => [
        'controller' => [WebApi::class, 'killsUpdate'],
        'params' => [
            'albionName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в albion'
            ],
            'killCount' => [
                'value' => 0,
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Кол-во убийств игроком'
            ],
        ],
    ],
    /**
     * Обновление кол-ва доната игроком
     *
     * @return Api,Redirect
     */
    'POST:donationUpdate' => [
        'controller' => [WebApi::class, 'donationUpdate'],
        'params' => [
            'albionName' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Имя пользователя в albion'
            ],
            'donation' => [
                'value' => 0,
                'filter' => FILTER_VALIDATE_INT,
                'comment' => 'Взнос игрока'
            ],
        ],
    ],
    /**
     * Отметка наград, полученных пользователем
     *
     * @return Api,Redirect
     */
    'GET:eventStat' => [
        'controller' => [WebApi::class, 'eventStat'],
        'params' => [
            'from' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Дата, от',
            ],
            'to' => [
                'value' => '',
                'filter' => FILTER_SANITIZE_STRING,
                'comment' => 'Дата, до',
            ],
        ],
        'local' => true,
    ],
];
