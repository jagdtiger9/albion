<?php

namespace Aljerom\Albion\Services;

use MagicPro\Database\Query\Raw;
use DateTime;
use Aljerom\Albion\Models\GuildMember;
use Aljerom\Albion\Models\Member;
use Aljerom\Albion\Models\MemberArchive;
use Aljerom\Albion\Models\MemberArchiveTmp;
use Aljerom\Albion\Models\MemberDaily;
use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\MemberArchiveRepository;
use Aljerom\Albion\Models\Repository\MemberDailyRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;

class AlbionImport
{
    public function guildImport($guildId = '', $newGuild = false): void
    {
        if ($guildId) {
            $guilds = (new GuildRepository())->getById($guildId);
            $guilds = $guilds ? [$guilds] : [];
        } else {
            $guilds = (new GuildRepository())->getOrderedList();
        }

        if ($guilds) {
            $albionApi = new AlbionApi();
            foreach ($guilds as $guild) {
                $guildInfo = $albionApi->guildInfo($guild->getField('id'))->first();
                if (!$guildInfo) {
                    $guild->markDeleted();
                } else {
                    $guildInfo = json_decode(json_encode($guildInfo), true);
                    $guild->update($guildInfo);

                    $guildMemberNames = [];
                    $archiveList = [];
                    $guildMembers = $albionApi->guildMembers($guild->getField('id'))->get();
                    echo 'Guild: ' . $guild->getField('name') . PHP_EOL;
                    echo ' * member count: ' . count($guildMembers) . PHP_EOL;
                    foreach ($guildMembers as $memberItem) {
                        $guildMemberNames[] = $memberItem->Name;
                        $memberRepository = new MemberRepository();
                        $member = $memberRepository->getById($memberItem->Id);
                        if (null !== $member) {
                            if ($memberItem->LifetimeStatistics->Timestamp > $member->getField('timestamp')
                                || $memberItem->GuildName !== $member->getField('guildName')) {
                                $memberRepository->saveMember($member, $memberItem);
                                $fields = $member->getFieldMap();
                                unset($fields['roles']);
                                // Save history
                                $archiveList[] = $fields;
                            }
                        } else {
                            $member = new Member();
                            // Если добавляется новая ги, флаг "пользователь добавлен в ги" не ставим
                            $memberRepository->saveMember($member, $memberItem, $newGuild ? false : true);
                            $fields = $member->getFieldMap();
                            unset($fields['roles']);
                            // Save history
                            $archiveList[] = $fields;
                        }
                    }
                    if ($archiveList) {
                        echo ' * add to archive: ' . count($archiveList) . PHP_EOL;
                        (new MemberArchive())->insertOrUpdate($archiveList);
                    }
                    if ($guildMemberNames) {
                        $archMember = null;
                        $archiveList = [];
                        $guildMember = new GuildMember($guild);
//app('log')->setLog('__.log')->save('members: ' . implode ("','", $guildMemberNames));
                        $goneMembers = (new MemberRepository())->getApiGoneMembers($guild, $guildMemberNames);
//app('log')->setLog('__.log')->save('gone: ' . count($goneMembers));
//app('log')->setLog('__.log')->save(var_export($guildMemberNames, true));
                        foreach ($goneMembers as $goneMember) {
//app('log')->setLog('__.log')->save(' * reset member: ' . $goneMember->name . '(' . $guild->name . ')');
                            echo ' * reset member: ' . $goneMember->name . '(' . $guild->name . ')' . PHP_EOL;
                            $guildMember->reset($goneMember);
                            $archMember = $goneMember->getFieldMap();
                            // Сбрасываем флаг guildIn (если игрок перешел в другую ги и сначала была
                            // обработана ситуация принятия в ги, флаг установлен)
                            $archMember['guildIn'] = 0;
                            unset($archMember['roles']);
                            $archiveList[] = $archMember;
                        }
                        if ($archiveList) {
                            echo ' * add reset to archive: ' . count($archiveList) . PHP_EOL;
                            (new MemberArchive())->insertOrUpdate($archiveList);
                        }
                    }
                    $guild->setUpdated();
                    echo $guild->getField('name') . PHP_EOL;
                }
                sleep(10);
            }
        }
    }

    public function dailyStat(): void
    {
        $memberDailyRepo = new MemberDailyRepository();
        $memberArchiveRepo = new MemberArchiveRepository();

        // Берем дату последнего обновления
        $lastUpdate = $memberDailyRepo->getLastUpdate();
        echo 'lastUpdate: ' . $lastUpdate . PHP_EOL;

        // Если - 0, производим начальную инициализацию
        // По дате первой записи в архиве получаем список записей для инициализации
        // Т.к. обновление происходит раз в сутки ночью 00:хх:yy, берем все что меньше 12:00:00
        if (!$lastUpdate) {
            if (!($nextUpdate = $memberArchiveRepo->getNextUpdate())) {
                return;
            }

            $dailyUsers = (new MemberArchive())
                ->where('lastActive_at', $nextUpdate)
                ->get();
            (new MemberDaily())->insertOrUpdate($dailyUsers);

            $lastUpdate = $nextUpdate;
        }

        if (!($nextUpdate = $memberArchiveRepo->getNextUpdate($lastUpdate))) {
            return;
        }
        echo 'nextUpdate: ' . $nextUpdate . PHP_EOL;
        (new MemberArchive())
            //->where('name', 'Aljerom')
            ->where('lastActive_at', $nextUpdate)
            ->chunk(
                1000,
                static function ($archMemberList) use ($nextUpdate) {
                    $memberIds = array_column($archMemberList, 'id');

                    $maxIds = (new MemberArchive())
                        ->whereIn('id', $memberIds)
                        ->where('lastActive_at', '<', $nextUpdate)
                        ->groupBy('id')
                        ->get(new Raw('max(`uid`) as maxUid'));
                    $prevMemberList = [];
                    if ($maxIds) {
                        $prevMemberList = (new MemberArchive())
                            ->whereIn('uid', array_column($maxIds, 'maxUid'))
                            ->get();
                        $prevMemberList = array_combine(
                            array_column($prevMemberList, 'id'),
                            $prevMemberList
                        );
                    }

                    $dailyUsers = [];
                    $exclude = [
                        'updated_at' => '',
                        'discordName' => '',
                        'discordId' => '',
                        'isTwink' => '',
                        'gm' => '',
                        'officer' => '',
                        'guardian' => '',
                        'rl' => '',
                        'roles' => '',
                        'killsDone' => '',
                        'donation' => '',
                    ];
                    var_dump($archMemberList);
                    foreach ($archMemberList as $archMember) {
                        $archMember = array_diff_key($archMember, $exclude);
                        $archMember['lastActive_at'] = $nextUpdate;
                        if (null !== $prevMember = $prevMemberList[$archMember['id']] ?? null) {
                            $userDelta = [
                                'killFame' => ($killFame = $archMember['killFame'] - $prevMember['killFame']) >= 0 ? $killFame : $prevMember['killFame'],
                                'deathFame' => ($deathFame = $archMember['deathFame'] - $prevMember['deathFame']) >= 0 ? $deathFame : $prevMember['deathFame'],
                                'pveTotal' => ($pveTotal = $archMember['pveTotal'] - $prevMember['pveTotal']) >= 0 ? $pveTotal : $prevMember['pveTotal'],
                                'craftingTotal' => ($craftingTotal = $archMember['craftingTotal'] - $prevMember['craftingTotal']) >= 0 ? $craftingTotal : $prevMember['craftingTotal'],
                                'gatheringTotal' => ($gatheringTotal = $archMember['gatheringTotal'] - $prevMember['gatheringTotal']) >= 0 ? $gatheringTotal : $prevMember['gatheringTotal'],
                                'fiberTotal' => ($fiberTotal = $archMember['fiberTotal'] - $prevMember['fiberTotal']) >= 0 ? $fiberTotal : $prevMember['fiberTotal'],
                                'hideTotal' => ($hideTotal = $archMember['hideTotal'] - $prevMember['hideTotal']) >= 0 ? $hideTotal : $prevMember['hideTotal'],
                                'oreTotal' => ($oreTotal = $archMember['oreTotal'] - $prevMember['oreTotal']) >= 0 ? $oreTotal : $prevMember['oreTotal'],
                                'rockTotal' => ($rockTotal = $archMember['rockTotal'] - $prevMember['rockTotal']) >= 0 ? $rockTotal : $prevMember['rockTotal'],
                                'woodTotal' => ($woodTotal = $archMember['woodTotal'] - $prevMember['woodTotal']) >= 0 ? $woodTotal : $prevMember['woodTotal'],
                            ];

                            $dailyUsers[] = array_merge($archMember, $userDelta);
                        } elseif ($archMember['killFame'] < 3000000 && $archMember['pveTotal'] < 3000000) {
                            // Если первая запись об игроке, только вступил в ги, условное ограничение на фейм
                            $dailyUsers[] = $archMember;
                        }
                    }

                    if ($dailyUsers) {
                        (new MemberDaily())->insertOrUpdate($dailyUsers);
                    }
                }
            );
    }

    public function initUpdatedAt(): void
    {
        (new MemberArchive())
            ->chunk(
                1000,
                static function ($memberList) {
                    $insert = [];
                    foreach ($memberList as $member) {
                        $member['lastActive_at'] = (new DateTime($member['timestamp']))
                            ->format('Y-m-d 00:00:00');
                        $insert[] = $member;
                    }
                    (new MemberArchiveTmp())->insertOrUpdate($insert);
                }
            );

        Member::chunk(
            1000,
            static function ($memberList) {
                foreach ($memberList as $member) {
                    $member->lastActive_at = (new DateTime($member->timestamp))
                        ->format(Member::UPDATED_AT_FORMAT);
                    $member->save();
                }
            }
        );
    }
}
