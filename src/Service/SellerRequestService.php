<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\Seller;
use App\Entity\SellerProfile;
use App\Entity\SellerRequest;
use App\Enum\SellerRequestStatus;
use App\Repository\SellerRepository;
use Doctrine\ORM\EntityManagerInterface;

final class SellerRequestService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SellerRepository $sellerRepository,
    ) {
    }

    public function hasSellerAccount(Customer $customer): bool
    {
        return null !== $this->sellerRepository->findByCustomer($customer);
    }

    public function getSellerForCustomer(Customer $customer): ?Seller
    {
        return $this->sellerRepository->findByCustomer($customer);
    }

    public function createRequest(Customer $customer, SellerRequest $requestData): SellerRequest
    {
        $existing = $this->sellerRepository->findByCustomer($customer);
        if (null !== $existing) {
            throw new \RuntimeException('У этого покупателя уже есть заявка или аккаунт продавца.');
        }

        $seller = new Seller()
            ->setEmail((string) $customer->getEmail())
            ->setCustomer($customer)
            ->setIsActive(false)
            ->setRoles(['ROLE_SELLER']);

        $profile = new SellerProfile()
            ->setShopName((string) $requestData->getShopName())
            ->setLegalName($requestData->getLegalName())
            ->setInn($requestData->getInn())
            ->setDescription($requestData->getDescription())
            ->setContactPhone($requestData->getContactPhone() ?? $customer->getPhone());

        $seller->setProfile($profile);

        $request = new SellerRequest()
            ->setSeller($seller)
            ->setStatus(SellerRequestStatus::Pending)
            ->setShopName((string) $requestData->getShopName())
            ->setLegalName($requestData->getLegalName())
            ->setInn($requestData->getInn())
            ->setDescription($requestData->getDescription())
            ->setContactPhone($requestData->getContactPhone() ?? $customer->getPhone());

        $seller->setRequest($request);

        $this->entityManager->persist($seller);
        $this->entityManager->persist($profile);
        $this->entityManager->persist($request);
        $this->entityManager->flush();

        return $request;
    }

    public function approve(SellerRequest $request, Admin $admin): Seller
    {
        if (SellerRequestStatus::Pending !== $request->getStatus()) {
            throw new \RuntimeException('Заявка уже обработана.');
        }

        $seller = $request->getSeller();
        if (null === $seller) {
            throw new \RuntimeException('Продавец не найден для заявки.');
        }

        $token = bin2hex(random_bytes(32));

        $request
            ->setStatus(SellerRequestStatus::Approved)
            ->setReviewedBy($admin)
            ->setReviewedAt(new \DateTimeImmutable())
            ->setRejectionReason(null);

        $seller
            ->setApprovedAt(new \DateTimeImmutable())
            ->setActivationToken($token)
            ->setActivationTokenExpiresAt(new \DateTimeImmutable('+7 days'));

        $this->entityManager->flush();

        return $seller;
    }

    public function reject(SellerRequest $request, Admin $admin, ?string $reason = null): void
    {
        if (SellerRequestStatus::Pending !== $request->getStatus()) {
            throw new \RuntimeException('Заявка уже обработана.');
        }

        $request
            ->setStatus(SellerRequestStatus::Rejected)
            ->setReviewedBy($admin)
            ->setReviewedAt(new \DateTimeImmutable())
            ->setRejectionReason($reason);

        $this->entityManager->flush();
    }

    public function activateSeller(Seller $seller, string $hashedPassword): void
    {
        $seller
            ->setPassword($hashedPassword)
            ->setIsActive(true)
            ->setActivationToken(null)
            ->setActivationTokenExpiresAt(null);

        $this->entityManager->flush();
    }
}
