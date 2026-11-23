<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class ValidSku extends Constraint
{
}
