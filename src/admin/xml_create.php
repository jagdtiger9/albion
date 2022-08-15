<?php

/**
 * ALTER TABLE `albion__members` ADD `discordName` VARCHAR(255) NOT NULL DEFAULT '' AFTER `timestamp`,
 * ADD `discordId` VARCHAR(255) NOT NULL DEFAULT '' AFTER `discordName`,
 * ADD `isTwink` TINYINT(1) NOT NULL DEFAULT '0' AFTER `discordId`,
 * ADD `gm` TINYINT(1) NOT NULL DEFAULT '0' AFTER `isTwink`,
 * ADD `officer` TINYINT(1) NOT NULL DEFAULT '0' AFTER `gm`,
 * ADD `guardian` TINYINT(1) NOT NULL DEFAULT '0' AFTER `officer`,
 * ADD `rl` TINYINT(1) NOT NULL DEFAULT '0' AFTER `guardian`,
 * ADD INDEX `discordId` (`discordId`);
 */

use dumpsite\Models\Dump;
use Aljerom\Albion\Models\EventMember;

$sql = " CREATE TABLE IF NOT EXISTS `albion__guilds` (
  `id` varchar(255) NOT NULL default '' COMMENT 'Идентификатор гильдии',
  `name` varchar(255) NOT NULL default '' COMMENT 'Название гильдии',
  `founderId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор основателя',
  `founderName` varchar(255) NOT NULL default '' COMMENT 'Имя основателя',
  `founded` varchar(100) NOT NULL default '' COMMENT 'ex: 2017-07-18T15:07:45.037338Z',
  `allianceId` varchar(255) NOT NULL default '' COMMENT 'Ид альянса',
  `allianceTag` varchar(255) NOT NULL default '' COMMENT 'Таг альянса',
  `allianceName` varchar(255) NOT NULL default '' COMMENT 'Название альнса',
  `killFame` bigint NOT NULL default 0 COMMENT '',
  `deathFame` bigint NOT NULL default 0 COMMENT '',
  `memberCount` integer NOT NULL default 0 COMMENT '',
  `isDeleted` tinyint(1) NOT NULL default 0 COMMENT 'Признак, 1 - гильдия удалена',
  `updatePriority` tinyint(1) NOT NULL default 0 COMMENT 'Признак, 1 - приоритетное обновление',
  `updatedAt` integer NOT NULL default '0' COMMENT '',
  UNIQUE `id` (`id`),
  INDEX `name` (`name`),
  INDEX `updatedAt` (`updatePriority`, `updatedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__guilds', $sql);

$sql = " CREATE TABLE IF NOT EXISTS `albion__members` (
  `id` varchar(255) NOT NULL default '' COMMENT 'Идентификатор игрока',
  `name` varchar(255) NOT NULL default '' COMMENT 'Имя игрока',
  `guildId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор гильдии',
  `guildName` varchar(255) NOT NULL default '' COMMENT 'Название гильдии',
  `allianceId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор альянса',
  `killFame` bigint NOT NULL default 0 COMMENT '',
  `deathFame` bigint NOT NULL default 0 COMMENT '',
  `pveTotal` bigint NOT NULL default 0 COMMENT '',
  `craftingTotal` bigint NOT NULL default 0 COMMENT '',
  `gatheringTotal` bigint NOT NULL default 0 COMMENT '',
  `fiberTotal` integer NOT NULL default 0 COMMENT '',
  `hideTotal` integer NOT NULL default 0 COMMENT '',
  `oreTotal` integer NOT NULL default 0 COMMENT '',
  `rockTotal` integer NOT NULL default 0 COMMENT '',
  `woodTotal` integer NOT NULL default 0 COMMENT '',
  `timestamp` varchar(100) NOT NULL default '' COMMENT 'ex: 2017-07-18T15:07:45.037338Z',
  `lastActive_at` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Дата последней активности',
  `activated` tinyint(1) NOT NULL default '0' COMMENT '0 - не активирована, 1 - активирована',
  `guildIn` tinyint(1) NOT NULL default '0' COMMENT 'Признак, игрок вступил в ги',
  `guildOut` varchar(255) NOT NULL default '' COMMENT 'Идентификатор гильдии из которой вышел игрок',  
  `updated_at` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Дата обновления записи',  
  `discordName` varchar(255) NOT NULL default '' COMMENT 'Имя игрока в discord',
  `discordId` varchar(255) NOT NULL default '' COMMENT 'ID игрока в discord',
  `isTwink` tinyint(1) NOT NULL default '0' COMMENT 'Учетная запись является твинком, признак',
  `gm` tinyint(1) not null default '0' COMMENT 'GameMaster role',
  `officer` tinyint(1) not null default '0' COMMENT 'Officer role',
  `guardian` tinyint(1) not null default '0' COMMENT 'Guardian role',
  `rl` tinyint(1) not null default '0' COMMENT 'RL role',
  `roles` text not null default '' COMMENT 'Список ролей пользователя в discord, JSON array',
  `killsDone` integer not null default '0' COMMENT 'Кол-во убийств',
  `donation` integer not null default '0' COMMENT 'Пожертвования в золоте',
  UNIQUE `id` (`id`),
  INDEX `name` (`name`),
  INDEX `guildId` (`guildId`),
  INDEX `discordId` (`discordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__members', $sql);

$sql = " CREATE TABLE IF NOT EXISTS `albion__playerReward` (
  `discordId` varchar(255) NOT NULL default '' COMMENT 'ID игрока в discord',
  `small_badge` tinyint(1) not null default '0' COMMENT 'Малый знак - 1 балл',
  `big_badge` tinyint(1) not null default '0' COMMENT 'Большой знак – 3 балла',
  `medal` tinyint(1) not null default '0' COMMENT 'Медаль игры – 5 баллов',
  `small_order` tinyint(1) not null default '0' COMMENT 'Малый орден - 10 баллов',
  `big_order` tinyint(1) not null default '0' COMMENT 'Большой орден - 25 баллов',
  `kill_small_badge` tinyint(1) not null default '0' COMMENT 'Малый знак - 1 балл',
  `kill_mid_badge` tinyint(1) not null default '0' COMMENT 'Средний знак - 2 балла',
  `kill_big_badge` tinyint(1) not null default '0' COMMENT 'Большой знак - 3 балла',
  `donate_small_badge` tinyint(1) not null default '0' COMMENT 'Малый знак - 1 балл',
  `donate_mid_badge` tinyint(1) not null default '0' COMMENT 'Средний знак - 2 балла',
  `donate_big_badge` tinyint(1) not null default '0' COMMENT 'Большой знак - 3 балла',
  UNIQUE `discordId` (`discordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__playerReward', $sql);

$sql = " CREATE TABLE IF NOT EXISTS `albion__playerRewardSnapshot` (
  `uid` varchar(100) NOT NULL default '' COMMENT 'Идентификатор снепшота',
  `discordId` varchar(255) NOT NULL default '' COMMENT 'ID игрока в discord',
  `fixed_at` integer not null default '0' COMMENT 'Время фиксации',
  `fixedByUser` integer not null default '0' COMMENT 'Пользователь производивший фиксацию баллов',
  `isLastFix` tinyint(1) not null default '0' COMMENT 'Признак, текущий фикс является последним',
  `small_badge` tinyint(1) not null default '0' COMMENT 'Малый знак - 1 балл',
  `big_badge` tinyint(1) not null default '0' COMMENT 'Большой знак – 3 балла',
  `medal` tinyint(1) not null default '0' COMMENT 'Медаль игры – 5 баллов',
  `small_order` tinyint(1) not null default '0' COMMENT 'Малый орден - 10 баллов',
  `big_order` tinyint(1) not null default '0' COMMENT 'Большой орден - 25 баллов',
  `kill_small_badge` tinyint(1) not null default '0' COMMENT 'Малый знак - 1 балл',
  `kill_mid_badge` tinyint(1) not null default '0' COMMENT 'Средний знак - 2 балла',
  `kill_big_badge` tinyint(1) not null default '0' COMMENT 'Большой знак - 3 балла',
  `donate_small_badge` tinyint(1) not null default '0' COMMENT 'Малый знак - 1 балл',
  `donate_mid_badge` tinyint(1) not null default '0' COMMENT 'Средний знак - 2 балла',
  `donate_big_badge` tinyint(1) not null default '0' COMMENT 'Большой знак - 3 балла',
  INDEX `fixedDiscor` (`fixed_at`, `discordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__playerRewardSnapshot', $sql);

$sql = " CREATE TABLE IF NOT EXISTS `albion__membersArchive` (
  `uid` integer NOT NULL auto_increment COMMENT 'Уникальный идентификатор',
  `id` varchar(255) NOT NULL default '' COMMENT 'Идентификатор игрока',
  `name` varchar(255) NOT NULL default '' COMMENT 'Имя игрока',
  `guildId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор гильдии',
  `guildName` varchar(255) NOT NULL default '' COMMENT 'Название гильдии',
  `allianceId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор альянса',
  `killFame` bigint NOT NULL default 0 COMMENT '',
  `deathFame` bigint NOT NULL default 0 COMMENT '',
  `pveTotal` bigint NOT NULL default 0 COMMENT '',
  `craftingTotal` bigint NOT NULL default 0 COMMENT '',
  `gatheringTotal` integer NOT NULL default 0 COMMENT '',
  `fiberTotal` integer NOT NULL default 0 COMMENT '',
  `hideTotal` integer NOT NULL default 0 COMMENT '',
  `oreTotal` integer NOT NULL default 0 COMMENT '',
  `rockTotal` integer NOT NULL default 0 COMMENT '',
  `woodTotal` integer NOT NULL default 0 COMMENT '',
  `timestamp` varchar(100) NOT NULL default '' COMMENT 'ex: 2017-07-18T15:07:45.037338Z',
  `lastActive_at` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Дата обновления записи',
  `activated` tinyint(1) NOT NULL default '0' COMMENT '0 - не активирована, 1 - активирована',
  `guildIn` tinyint(1) NOT NULL default '0' COMMENT 'Признак, игрок вступил в ги',
  `guildOut` varchar(255) NOT NULL default '' COMMENT 'Идентификатор гильдии из которой вышел игрок',
  `updated_at` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Дата обновления записи',
  `discordName` varchar(255) NOT NULL default '' COMMENT 'Имя игрока в discord',
  `discordId` varchar(255) NOT NULL default '' COMMENT 'ID игрока в discord',
  `isTwink` tinyint(1) NOT NULL default '0' COMMENT 'Учетная запись является твинком, признак',
  `gm` tinyint(1) not null default '0' COMMENT 'GameMaster role',
  `officer` tinyint(1) not null default '0' COMMENT 'Officer role',
  `guardian` tinyint(1) not null default '0' COMMENT 'Guardian role',
  `rl` tinyint(1) not null default '0' COMMENT 'RL role',
  `killsDone` integer not null default '0' COMMENT 'Кол-во убийств',
  `donation` integer not null default '0' COMMENT 'Пожертвования в золоте',
  PRIMARY KEY  (`uid`),
  INDEX `id` (`id`),
  INDEX `guildId` (`guildId`),
  INDEX `updated_at` (`updated_at`),
  UNIQUE `nameUpdate` (`name`, `lastActive_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__membersArchive', $sql);

$sql = " CREATE TABLE IF NOT EXISTS `albion__membersDaily` (
  `uid` integer NOT NULL default '0' COMMENT 'Уникальный идентификатор',
  `id` varchar(255) NOT NULL default '' COMMENT 'Идентификатор игрока',
  `name` varchar(255) NOT NULL default '' COMMENT 'Имя игрока',
  `guildId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор гильдии',
  `guildName` varchar(255) NOT NULL default '' COMMENT 'Название гильдии',
  `allianceId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор альянса',
  `killFame` integer NOT NULL default 0 COMMENT '',
  `deathFame` integer NOT NULL default 0 COMMENT '',
  `pveTotal` integer NOT NULL default 0 COMMENT '',
  `craftingTotal` integer NOT NULL default 0 COMMENT '',
  `gatheringTotal` integer NOT NULL default 0 COMMENT '',
  `fiberTotal` integer NOT NULL default 0 COMMENT '',
  `hideTotal` integer NOT NULL default 0 COMMENT '',
  `oreTotal` integer NOT NULL default 0 COMMENT '',
  `rockTotal` integer NOT NULL default 0 COMMENT '',
  `woodTotal` integer NOT NULL default 0 COMMENT '',
  `timestamp` varchar(100) NOT NULL default '' COMMENT 'ex: 2017-07-18T15:07:45.037338Z',
  `activated` tinyint(1) NOT NULL default '0' COMMENT '0 - не активирована, 1 - активирована',
  `lastActive_at` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Дата обновления записи',
  `guildIn` tinyint(1) NOT NULL default '0' COMMENT 'Признак, игрок вступил в ги',
  `guildOut` varchar(255) NOT NULL default '' COMMENT 'Идентификатор гильдии из которой вышел игрок',
  INDEX `idGuild` (`id`, `guildName`),
  UNIQUE `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__membersDaily', $sql);

$sql = " CREATE TABLE IF NOT EXISTS `albion__discordRegistration` (
  `discordId` varchar(255) NOT NULL default '' COMMENT 'ID игрока в discord',
  `discordName` varchar(255) NOT NULL default '' COMMENT 'Имя игрока в discord',
  `albionName` varchar(255) NOT NULL default '' COMMENT 'Имя игрока',
  `albionId` varchar(255) NOT NULL default '' COMMENT 'ID игрока',
  `isTwink` tinyint(1) NOT NULL default '0' COMMENT 'Игрок является твинком, признак',
  `guildName` varchar(255) NOT NULL default '' COMMENT 'Имя гильдии',
  `moderator` varchar(255) NOT NULL default '' COMMENT 'Имя игрока, подтвердившего заявку',
  `registered_at` integer NOT NULL default 0 COMMENT 'Время регистрации',
  `confirm_at` integer NOT NULL default 0 COMMENT 'Время подтверждения регистрации',
  UNIQUE `discordId` (`discordId`, `albionName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__discordRegistration', $sql);

$sql = " CREATE TABLE IF NOT EXISTS `albion__loginHash` (
  `discordId` varchar(255) NOT NULL default '' COMMENT 'ID игрока, для активации которого выдана ссылка',
  `instantLoginHash` varchar(255) NOT NULL default '' COMMENT 'Хеш для авторизации без пароля',
  `updated_at` integer NOT NULL default 0 COMMENT 'Время создания хеша',
  UNIQUE `discordId` (`discordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__loginHash', $sql);

$sql = " CREATE TABLE IF NOT EXISTS `albion__event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discordMessageId` varchar(255) NOT NULL default '' COMMENT 'ID сообщения дискорда',
  `linkHash` varchar(255) NOT NULL default '' COMMENT 'hash для инвайта игроков',
  `creatorId` varchar(255) NOT NULL default '' COMMENT 'Создатель, id',
  `creatorName` varchar(255) NOT NULL default '' COMMENT 'Создатель, имя',
  `rlId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор РЛ-а',
  `rlName` varchar(255) NOT NULL default '' COMMENT 'Имя РЛ-а',
  `name` varchar(255) NOT NULL default '' COMMENT 'Название активности',
  `type` varchar(30) NOT NULL default '' COMMENT 'Тип активности',
  `guildId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор гильдии',
  `allianceId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор альянса',
  `allowAlliance` tinyint(1) not null default '0' COMMENT '0 - активность только для гильдии, 1 - активность для всего альянса',
  `started_at` integer NOT NULL default 0 COMMENT '',
  `created_at` integer NOT NULL default 0 COMMENT '',
  `isMandatory` tinyint(1) NOT NULL default 1,
  `approved` tinyint(1) NOT NULL default '1' COMMENT 'Активность подтверждена и участвует в статистике',
  `factor` tinyint(1) NOT NULL default '1' COMMENT 'Коеффициент важности активности', 
  PRIMARY KEY (`id`),
  INDEX `guildTime` (`guildId`, `started_at`),
  INDEX `linkHash` (`linkHash`),
  INDEX `discordMessageId` (`discordMessageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__event', $sql);

$sql = " CREATE TABLE IF NOT EXISTS `albion__eventMember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` varchar(255) NOT NULL default '' COMMENT 'Событие, id',
  `memberId` varchar(255) NOT NULL default '' COMMENT 'Участник, id',
  `memberName` varchar(255) NOT NULL default '' COMMENT 'Участник, имя',
  `role` enum('', '" . implode("', '", array_keys(EventMember::ROLE_LIST)) . "') NOT NULL default '' COMMENT 'Роль участника',
  `isRl` tinyint(1) NOT NULL default '0' COMMENT '',
  `rlComment` varchar(255) NOT NULL default '' COMMENT 'Комментарий РЛа',
  `created_at` integer NOT NULL default 0 COMMENT '',
  PRIMARY KEY (`id`),
  UNIQUE `eventMember` (`eventId`, `memberId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__eventMember', $sql);

// TMP таблица
$sql = " CREATE TABLE IF NOT EXISTS `albion__membersArchiveTmp` (
  `uid` integer NOT NULL auto_increment COMMENT 'Уникальный идентификатор',
  `id` varchar(255) NOT NULL default '' COMMENT 'Идентификатор игрока',
  `name` varchar(255) NOT NULL default '' COMMENT 'Имя игрока',
  `guildId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор гильдии',
  `allianceId` varchar(255) NOT NULL default '' COMMENT 'Идентификатор альянса',
  `killFame` integer NOT NULL default 0 COMMENT '',
  `deathFame` integer NOT NULL default 0 COMMENT '',
  `pveTotal` integer NOT NULL default 0 COMMENT '',
  `craftingTotal` integer NOT NULL default 0 COMMENT '',
  `gatheringTotal` integer NOT NULL default 0 COMMENT '',
  `fiberTotal` integer NOT NULL default 0 COMMENT '',
  `hideTotal` integer NOT NULL default 0 COMMENT '',
  `oreTotal` integer NOT NULL default 0 COMMENT '',
  `rockTotal` integer NOT NULL default 0 COMMENT '',
  `woodTotal` integer NOT NULL default 0 COMMENT '',
  `timestamp` varchar(100) NOT NULL default '' COMMENT 'ex: 2017-07-18T15:07:45.037338Z',
  `activated` tinyint(1) NOT NULL default '0' COMMENT '0 - не активирована, 1 - активирована',
  `lastActive_at` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Дата обновления записи',
  PRIMARY KEY (`uid`),
  INDEX `id` (`id`),
  INDEX `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
Dump::createTable('albion__membersArchiveTmp', $sql);
