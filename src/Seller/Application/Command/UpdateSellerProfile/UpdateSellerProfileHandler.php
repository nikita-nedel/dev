<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\UpdateSellerProfile;

use App\Identity\Domain\Model\CustomerProfile;
use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\SellerRequest;
use App\Seller\Domain\Model\SellerRequestStatus;
use App\Seller\Domain\Repository\SellerRequestRepositoryInterface;
use App\Shared\Domain\Port\ClockInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class UpdateSellerProfileHandler
{
    public function __construct(
        private SellerRequestRepositoryInterface $requests,
        private TransactionManagerInterface $transactions,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(UpdateSellerProfile $command): Seller
    {
        $seller = $command->seller;
        $customer = $seller->getCustomer();

        if (null === $customer) {
            throw new \RuntimeException('Не удалось найти покупателя, к которому привязан магазин.');
        }

        $data = $command->data;

        $customerProfile = $customer->getProfile();

        if (null === $customerProfile) {
            $customerProfile = new CustomerProfile();
            $customer->setProfile($customerProfile);
        }

        $customerProfile
            ->setFirstName(trim($data->firstName))
            ->setLastName(trim($data->lastName))
            ->setPhone(trim($data->phone));

        $request = $this->requests->findBySeller($seller);

        if (null === $request) {
            $request = new SellerRequest()
                ->setSeller($seller)
                ->setStatus(SellerRequestStatus::Approved)
                ->setReviewedAt($this->clock->now());
            $this->requests->add($request);
        }

        $request
            ->setShopName(trim($data->shopName))
            ->setLegalName($this->nullable($data->legalName))
            ->setInn($this->nullable($data->inn))
            ->setDescription($this->nullable($data->description))
            ->setContactPhone($this->nullable($data->contactPhone));

        // Профиль покупателя и заявка — управляемые сущности, транзакция сохранит их одним flush.
        $this->transactions->transactional(static function (): void {
        });

        return $seller;
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }
}
