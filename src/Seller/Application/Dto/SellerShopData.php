<?php

declare(strict_types=1);

namespace App\Seller\Application\Dto;

final class SellerShopData
{
    public string $shopName = '';

    public ?string $legalName = null;

    public ?string $inn = null;

    public ?string $description = null;

    public ?string $contactPhone = null;
}
