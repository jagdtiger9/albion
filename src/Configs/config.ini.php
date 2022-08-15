<?php

return [
    'mod_config' => [
        // Доступность в списке модулей админки
        'isVisible' => false,

        // Наименование модуля
        'moduleTitle' => 'Albion online',

        // Список автоматически создаваемых директорий модуля, доступных для записи
        // Каждый элемент массива - путь, относительно DOCUMENT_ROOT/vardata/modules/<moduleName>
        'vardata' => [],
        // Директории дампируемых данных. Каждый элемент массива - путь к директории, относительно DOCUMENT_ROOT
        'dumpFiles' => [],
    ],

    'tables' => [
        'guilds' => 'albion__guilds',
        'members' => 'albion__members',
        'membersArchive' => 'albion__membersArchive',
        'membersArchiveTmp' => 'albion__membersArchiveTmp',
        'membersDaily' => 'albion__membersDaily',
        'event' => 'albion__event',
        'eventMember' => 'albion__eventMember',
        'loginHash' => 'albion__loginHash',
        'discordRegistration' => 'albion__discordRegistration',
        'playerReward' => 'albion__playerReward',
        'playerRewardSnapshot' => 'albion__playerRewardSnapshot',
    ],

    'serverName' => 'albion.gudilap.ru',
    'guildName' => 'OCEAN',
    'guildId' => '9ovaHeVdS0KvvGnpz-uT3w',
];
