<?php

namespace Aljerom\Albion\Infrastructure\Controllers;

use Aljerom\Albion\Models\Repository\GuildRepository;
use Aljerom\Albion\Models\Repository\MemberRepository;
use App\DomainModel\ValueObject\LoginVO;
use Exception;
use InvalidArgumentException;
use MagicPro\Application\Controller;
use MagicPro\Database\Exception\DbException;
use MagicPro\Http\Api\ErrorResponse;
use MagicPro\Http\Api\SuccessResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use sessauth\Domain\Repository\UserRepositoryInterface;
use sessauth\Services\UserRemove;

class AdminAction extends Controller
{
    public function actionDelGuild(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $repository = new GuildRepository();
            if (false !== $uid = $request->Get('id')) {
                if (null === $guild = $repository->getById($uid)) {
                    throw new InvalidArgumentException('Гильдия с указанным идентификатором не найдена');
                }
            } else {
                throw new InvalidArgumentException('Не задан идентификатор гильдии');
            }

            if (false === $guild->delete()) {
                throw new DbException('Ошибка удаления гильдии');
            }

            $apiResponse = new SuccessResponse('Гильдия успешно удалена');
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }

    public function actionDelMember(
        ServerRequestInterface $request,
        UserRepositoryInterface $userRepo,
    ): ResponseInterface {
        $requestParams = $request->getQueryParams();
        try {
            if (!($id = $requestParams['id'])) {
                throw new InvalidArgumentException('Не указан ID игрока');
            }
            $repo = new MemberRepository();
            if (null === $player = $repo->getById($id)) {
                throw new InvalidArgumentException('Игрок с указанным ID не найден');
            }

            $login = $player->getField('name');
            if (null === $user = $userRepo->getByLogin(new LoginVO($login))) {
                throw new InvalidArgumentException('У данного игрока нет привязанной учетной записи');
            }
            (new UserRemove($user))->remove();
            $player->setDeactive();

            $apiResponse = new SuccessResponse('Учетная запись игрока удалена');
        } catch (Exception $e) {
            $apiResponse = ErrorResponse::fromException($e);
        }

        return $this->setApiResponse($request, $apiResponse->withRedirect());
    }
}
