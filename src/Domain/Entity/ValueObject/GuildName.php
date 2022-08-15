<?php

namespace albion\Domain\Entity\ValueObject;

class GuildName
{
    private $id;

    private $name;

    private $allianceId;

    public function __construct(string $id, string $name, string $allianceId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->allianceId = $allianceId;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function allianceId(): string
    {
        return $this->allianceId;
    }
}
