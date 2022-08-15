<?php

namespace Aljerom\Albion\Models\Repository;

use MagicPro\Database\Model\Repository\Repository;
use Aljerom\Albion\Models\Guild;

class GuildRepository extends Repository
{
    protected $modelClass = Guild::class;

    /**
     * @var Guild
     */
    protected $model;

    public function getOrderedList()
    {
        return $this->builder
            ->orderBy('updatedAt')
            ->orderBy('updatePriority', 'desc')
            ->get();
    }

    public function saveGuild($data): bool
    {
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }
        foreach ($data as $k => $v) {
            $data[lcfirst($k)] = $v ?? '';
        }

        return $this->save($data);
    }

    public function search($name): array
    {
        return $this->builder
            ->where('name', 'like', $name . '%')
            ->get();
    }
}
