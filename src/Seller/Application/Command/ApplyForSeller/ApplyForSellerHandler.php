<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\ApplyForSeller;

use App\Identity\Domain\Model\Seller;
use App\Identity\Domain\Model\SellerProfile;
use App\Identity\Domain\Repository\SellerRepositoryInterface;
use App\Seller\Domain\Model\SellerRequest;
use App\Seller\Domain\Model\SellerRequestStatus;
use App\Seller\Domain\Repository\SellerRequestRepositoryInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class ApplyForSellerHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellers,
        private SellerRequestRepositoryInterface $requests,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(ApplyForSeller $command): SellerRequest
    {
        if (null !== $this->sellers->findByCustomer($command->customer)) {
            throw new \RuntimeException('У этого покупателя уже есть заявка или аккаунт продавца.');
        }

        $seller = (new Seller())
            ->setEmail((string) $command->customer->getEmail())
            ->setCustomer($command->customer)
            ->setIsActive(false)
            ->setRoles(['ROLE_SELLER']);

        $request = $command->request;
        $contactPhone = $request->getContactPhone() ?? $command->customer->getPhone();

        $seller->setProfile(
            (new SellerProfile())
                ->setShopName((string) $request->getShopName())
                ->setLegalName($request->getLegalName())
                ->setInn($request->getInn())
                ->setDescription($request->getDescription())
                ->setContactPhone($contactPhone),
        );

        $request
            ->setSeller($seller)
            ->setStatus(SellerRequestStatus::Pending)
            ->setContactPhone($contactPhone);

        $this->transactions->transactional(function () use ($seller, $request): void {
            $this->sellers->add($seller);
            $this->requests->add($request);
        });

        return $request;
    }
}
