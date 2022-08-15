<?php

namespace albion\Services;

use InvalidArgumentException;
use albion\Domain\Exception\AlbionException;
use albion\Models\Member;
use albion\Models\Privilege\MemberPrivilege;
use albion\Models\Repository\MemberRepository;

class MemberInGameStat
{
    /**
     * Изменение кол-ва убийств игрока
     *
     * @param $memberName
     * @param $killCount
     * @return Member
     * @throws AlbionException
     */
    public function setKills($memberName, $killCount): Member
    {
        if (!$memberName) {
            throw new InvalidArgumentException('Не указано имя игрока');
        }
        $repo = new MemberRepository();
        if (null === $player = $repo->getBy('name', $memberName)) {
            throw new InvalidArgumentException('Игрок ' . $memberName . ' не найден');
        }

        if (!(new MemberPrivilege())->isOfficer()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 405);
        }

        if (!$player->setKills($killCount)->save()) {
            throw new AlbionException('Ошибка изменения кол-ва убийств', 13);
        }

        return $player;
    }

    /**
     * Изменение кол-ва доната игрока
     *
     * @param $memberName
     * @param $donation
     * @return Member
     * @throws AlbionException
     */
    public function setDonation($memberName, $donation): Member
    {
        if (!$memberName) {
            throw new InvalidArgumentException('Не указано имя игрока');
        }
        $repo = new MemberRepository();
        if (null === $player = $repo->getBy('name', $memberName)) {
            throw new InvalidArgumentException('Игрок ' . $memberName . ' не найден');
        }

        if (!(new MemberPrivilege())->isOfficer()) {
            throw new AlbionException('Недостаточно прав для выполнения операции', 405);
        }

        if (!$player->setDonation($donation)->save()) {
            throw new AlbionException('Ошибка изменения размера доната игрока', 15);
        }

        return $player;
    }
}
