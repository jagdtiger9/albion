<?php

namespace Aljerom\Albion\Application\CommandHandler;

use App\DomainModel\ValueObject\Price;
use MagicPro\Contracts\Session\SessionInterface;
use MagicPro\Contracts\User\CurrentUserInterface;
use MagicPro\DomainModel\ORM\EntityManagerInterface;
use MagicPro\Messenger\Handler\MessageHandlerInterface;
use payment\Domain\Entity\Identity\OrderUuid;
use payment\Domain\Entity\Identity\PackageId;
use payment\Domain\Entity\Identity\PromoOfferId;
use payment\Domain\Entity\Identity\SessionId;
use payment\Domain\Entity\Identity\UserId;
use payment\Domain\Entity\Order;
use payment\Domain\Entity\ValueObject\ProductSubscriptionVO;
use payment\Domain\Entity\ValueObject\ProductTypeVO;
use payment\Domain\Exception\PaymentException;
use payment\Domain\Repository\OrderRepositoryInterface;
use payment\Domain\Repository\PackageRepositoryInterface;
use payment\Domain\Repository\PromoOfferRepositoryInterface;
use Aljerom\Albion\Application\Command\CreateSnapshotCommand;

class CreateSnapshotCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private SessionInterface              $session,
        private CurrentUserInterface          $user,
        private OrderRepositoryInterface      $orderRepo,
        private PackageRepositoryInterface    $packageRepo,
        private PromoOfferRepositoryInterface $promoOfferRepo,
        private EntityManagerInterface        $entityManager,
    ) {
    }

    /**
     * @param CreateSnapshotCommand $command
     * @return OrderUuid
     * @throws PaymentException
     */
    public function __invoke(CreateSnapshotCommand $command): OrderUuid
    {
        $payType = new ProductTypeVO($command->itemType);
        if ($payType->getType() === ProductTypeVO::PACKAGE) {
            $packageId = new PackageId($command->itemId);
            if (null === $package = $this->packageRepo->find($packageId)) {
                throw new PaymentException('Указанный пакет не существует, ' . $packageId->getId());
            }
            $title = $package->title();
            $comment = $command->comment;
            $price = 0;
            $promoOfferId = null;
            $isSubscription = $package->isSubscription();

            // Промо предложение, актуально для подписок
            // на данный момент - 1 месяц за рубль и далее списание по 99р
            if ($command->usePromoOffer) {
                $promoOffer = $this->promoOfferRepo->findActivated(
                    new PromoOfferId($command->usePromoOffer),
                    new UserId($this->user->uid())
                );
                if (null === $promoOffer) {
                    throw new PaymentException('Промо предложение не найдено, ' . $command->usePromoOffer);
                }

                if (!$promoOffer->promoOfferUsers()) {
                    $price = $promoOffer->total();
                    $promoOfferId = $promoOffer->uid();
                }
            }
            if (!$price) {
                $price = $package->price();
            }
        } else {
            $title = $command->comment;
            $comment = $command->comment;
            $price = new Price($command->total);
            $packageId = new PackageId($command->itemId);
            $promoOfferId = null;
            $isSubscription = false;
        }

        $order = $this->orderRepo->findOpened(
            new SessionId($this->session->sid()),
            $payType,
            $packageId,
            $price
        );

        if (null === $order) {
            $order = new Order(
                $this->orderRepo->nextIdentity(),
                new SessionId($this->session->sid()),
                new UserId($this->user->uid()),
                $payType,
                $packageId,
                $price,
                new ProductSubscriptionVO($isSubscription, false)
            );
        }
        $order->changeDescription($title, $comment);
        if ($promoOfferId) {
            $order->setPromoOffer($promoOfferId);
        }
        $order->setAddParams($command->userEmail, $command->addParam, $command->article);
        $this->entityManager->persist($order);

        return $order->uuid();
    }
}
