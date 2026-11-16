<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\ActivateSeller;

use App\Identity\Domain\Model\Seller;

/**
 * Команда «активировать продавца»: установить пароль по одноразовой ссылке.
 */
final readonly class ActivateSeller
{
    public function __construct(
        public Seller $seller,
        public string $plainPassword,
    ) {
    }
}
