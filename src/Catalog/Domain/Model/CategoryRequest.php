<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;

use App\Identity\Domain\Model\Admin;
use App\Identity\Domain\Model\Seller;
use App\Shared\Domain\Exception\DomainException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: 'category_request')]
#[ORM\Index(name: 'idx_category_request_status', columns: ['status'])]
#[ORM\Index(name: 'idx_category_request_seller', columns: ['seller_id'])]
class CategoryRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(length: 16, enumType: CategoryRequestStatus::class)]
    private CategoryRequestStatus $status = CategoryRequestStatus::Pending;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Category $createdCategory = null;

    #[ORM\ManyToOne(targetEntity: Admin::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Admin $reviewedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeImmutable $createdAt;

    private function __construct(
        #[ORM\ManyToOne(targetEntity: Seller::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Seller $seller,
        #[ORM\ManyToOne(targetEntity: Category::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
        private ?Category $parent,
        string $name,
        ?string $comment,
    ) {
        $this->name = Category::normalizeName($name);
        $this->comment = self::trimToNull($comment);
    }

    public static function submit(Seller $seller, ?Category $parent, string $name, ?string $comment = null): self
    {
        return new self($seller, $parent, $name, $comment);
    }

    public function approve(Admin $admin, Category $category, \DateTimeImmutable $now): void
    {
        $this->assertPending();

        $this->status = CategoryRequestStatus::Approved;
        $this->createdCategory = $category;
        $this->reviewedBy = $admin;
        $this->reviewedAt = $now;
        $this->rejectionReason = null;
    }

    public function reject(Admin $admin, string $reason, \DateTimeImmutable $now): void
    {
        $this->assertPending();

        $reason = trim($reason);

        if ('' === $reason) {
            throw new DomainException('Укажите причину отклонения.');
        }

        $this->status = CategoryRequestStatus::Rejected;
        $this->reviewedBy = $admin;
        $this->reviewedAt = $now;
        $this->rejectionReason = $reason;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeller(): Seller
    {
        return $this->seller;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getStatus(): CategoryRequestStatus
    {
        return $this->status;
    }

    public function getCreatedCategory(): ?Category
    {
        return $this->createdCategory;
    }

    public function getReviewedBy(): ?Admin
    {
        return $this->reviewedBy;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    private function assertPending(): void
    {
        if (CategoryRequestStatus::Pending !== $this->status) {
            throw new DomainException('Заявка уже обработана.');
        }
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
