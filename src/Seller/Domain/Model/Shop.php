<?php

declare(strict_types=1);

namespace App\Seller\Domain\Model;

use App\Identity\Domain\Model\Seller;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'shop')]
#[ORM\Index(name: 'idx_shop_seller', columns: ['seller_id'])]
class Shop
{
    public const int NAME_MAX_LENGTH = 255;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legalName = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $inn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        #[ORM\ManyToOne(targetEntity: Seller::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Seller $seller,
        string $name,
        ?string $legalName,
        ?string $inn,
        ?string $description,
        ?string $contactPhone,
    ) {
        $now = new \DateTimeImmutable();
        $this->name = self::normalizeName($name);
        $this->legalName = self::trimToNull($legalName);
        $this->inn = self::normalizeInn($inn);
        $this->description = self::trimToNull($description);
        $this->contactPhone = self::trimToNull($contactPhone);
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public static function open(
        Seller $seller,
        string $name,
        ?string $legalName = null,
        ?string $inn = null,
        ?string $description = null,
        ?string $contactPhone = null,
    ): self {
        return new self($seller, $name, $legalName, $inn, $description, $contactPhone);
    }

    public function update(
        string $name,
        ?string $legalName,
        ?string $inn,
        ?string $description,
        ?string $contactPhone,
    ): void {
        $this->name = self::normalizeName($name);
        $this->legalName = self::trimToNull($legalName);
        $this->inn = self::normalizeInn($inn);
        $this->description = self::trimToNull($description);
        $this->contactPhone = self::trimToNull($contactPhone);
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function belongsTo(Seller $seller): bool
    {
        if ($this->seller === $seller) {
            return true;
        }

        $sellerId = $this->seller->getId();

        return null !== $sellerId && $sellerId === $seller->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeller(): Seller
    {
        return $this->seller;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLegalName(): ?string
    {
        return $this->legalName;
    }

    public function getInn(): ?string
    {
        return $this->inn;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private static function normalizeName(string $name): string
    {
        $name = trim($name);

        if ('' === $name) {
            throw new \InvalidArgumentException('Название магазина не может быть пустым.');
        }

        if (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            $message = sprintf('Название магазина не может быть длиннее %d символов.', self::NAME_MAX_LENGTH);

            throw new \InvalidArgumentException($message);
        }

        return $name;
    }

    private static function normalizeInn(?string $inn): ?string
    {
        $inn = self::trimToNull($inn);

        if (null === $inn) {
            return null;
        }

        if (1 !== preg_match('/^(?:\d{10}|\d{12})$/', $inn)) {
            throw new \InvalidArgumentException('ИНН должен состоять из 10 или 12 цифр.');
        }

        return $inn;
    }

    private static function trimToNull(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }
}
