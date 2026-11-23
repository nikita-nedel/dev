<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Validation;

use App\Catalog\Domain\ValueObject\Sku;
use App\Shared\Domain\Exception\DomainException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ValidSkuValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidSku) {
            throw new UnexpectedTypeException($constraint, ValidSku::class);
        }

        if (null === $value || '' === $value || !is_scalar($value)) {
            return;
        }

        try {
            Sku::fromString((string) $value);
        } catch (DomainException $exception) {
            $this->context->buildViolation($exception->getMessage())->addViolation();
        }
    }
}
