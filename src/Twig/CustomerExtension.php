<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Customer;
use App\Entity\Seller;
use App\Repository\SellerRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CustomerExtension extends AbstractExtension
{
    public function __construct(
        private readonly SellerRepository $sellerRepository,
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
