<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Form;

use App\Catalog\Domain\Model\Product;
use App\Seller\Domain\Model\Shop;

final class ProductFormData
{
    public ?Shop $shop = null;

    public string $name = '';

    public string $sku = '';

    public string $price = '';

    public ?string $description = null;

    public static function fromProduct(Product $product, Shop $shop): self
    {
        $data = new self();
        $data->shop = $shop;
        $data->name = $product->getName();
        $data->sku = $product->getSku();
        $data->price = $product->getPriceDecimal();
        $data->description = $product->getDescription();

        return $data;
    }
}
