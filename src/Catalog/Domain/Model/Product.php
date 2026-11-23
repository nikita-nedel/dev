<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;

use App\Catalog\Domain\ValueObject\Sku;
use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\ValueObject\Money;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product')]
#[ORM\UniqueConstraint(name: 'uniq_product_shop_sku', columns: ['shop_id', 'sku'])]
#[ORM\Index(name: 'idx_product_shop_status', columns: ['shop_id', 'status'])]
class Product
{
    public const string CURRENCY = 'BYN';
    public const int NAME_MAX_LENGTH = 200;
    public const int DESCRIPTION_MAX_LENGTH = 5000;
    public const int MAX_PRICE_AMOUNT = 999_999_999;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private string $name;

    #[ORM\Column(length: 64)]
    private string $sku;

    #[ORM\Column]
    private int $priceAmount;

    #[ORM\Column(length: 3)]
    private string $priceCurrency;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 16, enumType: ProductStatus::class)]
    private ProductStatus $status = ProductStatus::Draft;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        #[ORM\Column]
        private int $shopId,
        string $name,
        Sku $sku,
        Money $price,
        ?string $description,
    ) {
        self::guardPrice($price);

        $now = new \DateTimeImmutable();

        $this->name = self::normalizeName($name);
        $this->sku = $sku->value();
        $this->priceAmount = $price->amount();
        $this->priceCurrency = $price->currency();
        $this->description = self::normalizeDescription($description);
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public static function create(
        int $shopId,
        string $name,
        Sku $sku,
        Money $price,
        ?string $description = null,
    ): self {
        return new self($shopId, $name, $sku, $price, $description);
    }

    public function rename(string $name): void
    {
        $this->assertEditable();
        $this->name = self::normalizeName($name);
        $this->touch();
    }

    public function changeSku(Sku $sku): void
    {
        $this->assertEditable();
        $this->sku = $sku->value();
        $this->touch();
    }

    public function changePrice(Money $price): void
    {
        $this->assertEditable();
        self::guardPrice($price);

        $this->priceAmount = $price->amount();
        $this->priceCurrency = $price->currency();
        $this->touch();
    }

    public function changeDescription(?string $description): void
    {
        $this->assertEditable();
        $this->description = self::normalizeDescription($description);
        $this->touch();
    }

    public function publish(): void
    {
        $this->assertEditable();

        if (ProductStatus::Draft !== $this->status) {
            throw new DomainException('Опубликовать можно только черновик.');
        }

        $this->status = ProductStatus::Active;
        $this->touch();
    }

    public function archive(): void
    {
        if (ProductStatus::Archived === $this->status) {
            throw new DomainException('Товар уже в архиве.');
        }

        $this->status = ProductStatus::Archived;
        $this->touch();
    }

    public function assertEditable(): void
    {
        if (ProductStatus::Archived === $this->status) {
            throw new DomainException('Архивный товар нельзя редактировать.');
        }
    }

    public function isArchived(): bool
    {
        return ProductStatus::Archived === $this->status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getPrice(): Money
    {
        return Money::of($this->priceAmount, $this->priceCurrency);
    }

    public function getPriceDecimal(): string
    {
        return $this->getPrice()->toDecimalString();
    }

    public function getPriceFormatted(): string
    {
        return $this->getPrice()->format();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    private static function guardPrice(Money $price): void
    {
        if (self::CURRENCY !== $price->currency()) {
            throw new DomainException(sprintf('Цена товара должна быть в %s.', self::CURRENCY));
        }

        if ($price->isZero() || $price->isNegative()) {
            throw new DomainException('Цена должна быть больше нуля.');
        }

        if ($price->amount() > self::MAX_PRICE_AMOUNT) {
            throw new DomainException('Цена слишком большая.');
        }
    }

    private static function normalizeName(string $name): string
    {
        $name = trim($name);

        if ('' === $name) {
            throw new DomainException('Название товара не может быть пустым.');
        }

        if (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            $message = sprintf(
                'Название товара не может быть длиннее %d символов.',
                self::NAME_MAX_LENGTH,
            );

            throw new DomainException($message);
        }

        return $name;
    }

    private static function normalizeDescription(?string $description): ?string
    {
        if (null === $description) {
            return null;
        }

        $description = trim($description);

        if ('' === $description) {
            return null;
        }

        if (mb_strlen($description) > self::DESCRIPTION_MAX_LENGTH) {
            $message = sprintf(
                'Описание не может быть длиннее %d символов.',
                self::DESCRIPTION_MAX_LENGTH,
            );

            throw new DomainException($message);
        }

        return $description;
    }
}
