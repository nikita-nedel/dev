<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Twig;

use App\Identity\Domain\Model\Customer;
use App\Identity\Domain\Model\Seller;
use App\Identity\Domain\Repository\SellerRepositoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CustomerExtension extends AbstractExtension
{
    public function __construct(
        private readonly SellerRepositoryInterface $sellerRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('customer_seller', [$this, 'getSellerForCustomer']),
        ];
    }

    public function getSellerForCustomer(?Customer $customer): ?Seller
    {
        if (null === $customer) {
            return null;
        }

        return $this->sellerRepository->findByCustomer($customer);
    }
}
