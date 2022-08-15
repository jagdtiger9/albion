<?php

namespace Aljerom\Albion\Domain\Entity\ValueObject;

class PlayerFame
{
    public $killFame;

    public $deathFame;

    public $pveTotal;

    public $craftingTotal;

    public $gatheringTotal;

    public $fiberTotal;

    public $hideTotal;

    public $oreTotal;

    public $rockTotal;

    public $woodTotal;

    public function __construct(
        int $killFame,
        int $deathFame,
        int $pveTotal,
        int $craftingTotal,
        int $gatheringTotal,
        int $fiberTotal,
        int $hideTotal,
        int $oreTotal,
        int $rockTotal,
        int $woodTotal
    ) {
        $this->killFame = $killFame;
        $this->deathFame = $deathFame;
        $this->pveTotal = $pveTotal;
        $this->craftingTotal = $craftingTotal;
        $this->gatheringTotal = $gatheringTotal;
        $this->fiberTotal = $fiberTotal;
        $this->hideTotal = $hideTotal;
        $this->oreTotal = $oreTotal;
        $this->rockTotal = $rockTotal;
        $this->woodTotal = $woodTotal;
    }
}
