<?php

namespace albion\Domain\ReadModel;

use MagicPro\DomainModel\Dto\SimpleDto;

class PlayerRolesDTO extends SimpleDto
{
    public $ducklings;

    public $recruit;

    public $guild;

    public $advanced;

    public $officer;
}
