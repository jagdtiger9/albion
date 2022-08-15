<?php

use albion\Infrastructure\Controllers\Scheduler;

return [
    'importUsers' => [
        'title' => 'Импорт БД пользователей',
        'controller' => [Scheduler::class, 'ImportUsers'],
        'time' => '14,44 * * * * *'
    ],
    'importOceanUsers' => [
        'title' => 'Импорт БД пользователей Ocean',
        'controller' => [Scheduler::class, 'ImportOceanUsers'],
        'time' => '22 * * * * *'
    ],
    'dailyStat' => [
        'title' => 'Пересчет ежедневной статистики',
        'controller' => [Scheduler::class, 'DailyStat'],
        'time' => '02 11,12,13 * * * *'
    ],
    'discordInfoUpdate' => [
        'title' => 'Обновление информации из discord',
        'controller' => [Scheduler::class, 'DiscordInfoUpdate'],
        'time' => '17 */6 * * * *'
    ],
];
