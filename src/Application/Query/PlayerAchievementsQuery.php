<?php

namespace albion\Application\Query;

use App\DomainModel\Dto\PaginatorDto;
use MagicPro\Messenger\Message\QueryInterface;
use MagicPro\Messenger\Validation\MessageValidationInterface;
use MagicPro\Messenger\Validation\MessageValidationTrait;
use MagicPro\DomainModel\Dto\SimpleDto;
use albion\Application\QueryHandler\PlayerAchievementsQueryHandler;

class PlayerAchievementsQuery extends SimpleDto implements QueryInterface, MessageValidationInterface
{
    use MessageValidationTrait;

    /**
     * Название гильдии
     * @var string
     */
    public $guildName;

    /**
     * @var int
     */
    public $page = 0;

    /**
     * @var int
     */
    public $perPage = 200;

    /**
     * @var PaginatorDto
     */
    public $paginator;

    public function __construct()
    {
        $this->paginator = new PaginatorDto($this->page, $this->perPage);
    }

    public static function fromArray(array $fieldMap): AwardListQuery
    {
        /**
         * @var AwardListQuery $dto
         */
        $dto = parent::fromArray($fieldMap);
        $dto->paginator = new PaginatorDto($dto->page, $dto->perPage);

        return $dto;
    }

    public function preValidate(array $data): array
    {
        if ($data['p'] ?? null) {
            $data['page'] = $data['p'];
        }

        return $data;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'guildName' => 'required|string',
        ];
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getHandlerClassName(): string
    {
        return PlayerAchievementsQueryHandler::class;
    }
}
