<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\UpdateSellerPersonalData;

use App\Identity\Domain\Model\CustomerProfile;
use App\Identity\Domain\Model\Seller;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UpdateSellerPersonalDataHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UpdateSellerPersonalData $command): Seller
    {
        $seller = $command->seller;
        $customer = $seller->getCustomer();

        if (null === $customer) {
            throw new \RuntimeException('Не удалось найти покупателя, к которому привязан продавец.');
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

        $this->entityManager->flush();

        return $seller;
    }
}
