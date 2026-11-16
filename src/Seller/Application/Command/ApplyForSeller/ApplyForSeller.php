<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\ApplyForSeller;

use App\Identity\Domain\Model\Customer;
use App\Seller\Domain\Model\SellerRequest;

/**
 * Команда «подать заявку на продажу».
 *
 * Несёт покупателя и заполненную форму заявки (data_class формы SellerRequest).
 */
final readonly class ApplyForSeller
{
    public function __construct(
        public Customer $customer,
        public SellerRequest $request,
    ) {
    }
}
