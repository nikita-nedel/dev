<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;

use App\Shared\Domain\Exception\DomainException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: 'category')]
#[ORM\UniqueConstraint(name: 'uniq_category_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_category_parent', columns: ['parent_id'])]
class Category
{
    public const int NAME_MAX_LENGTH = 120;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    private ?self $parent = null;

    /** @var Collection<int, self> */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['sortOrder' => 'ASC', 'name' => 'ASC'])]
    private Collection $children;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'update')]
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        ?self $parent,
        string $name,
        #[ORM\Column(length: 140)]
        private string $slug,
        ?string $description,
        ?string $icon,
        #[ORM\Column]
        private int $sortOrder,
    ) {
        // Единственный источник правды про два уровня.
        if (null !== $parent && null !== $parent->getParent()) {
            throw new DomainException('Поддерживается максимум два уровня: категория → подкатегория.');
        }

        $this->parent = $parent;
        $this->name = self::normalizeName($name);
        $this->description = self::trimToNull($description);
        $this->icon = self::trimToNull($icon);
        $this->children = new ArrayCollection();
    }

    public static function create(
        ?self $parent,
        string $name,
        string $slug,
        ?string $description = null,
        ?string $icon = null,
        int $sortOrder = 0,
    ): self {
        return new self($parent, $name, $slug, $description, $icon, $sortOrder);
    }

    public function rename(string $name): void
    {
        $this->name = self::normalizeName($name);
    }

    public function updateMeta(?string $description, ?string $icon, int $sortOrder): void
    {
        $this->description = self::trimToNull($description);
        $this->icon = self::trimToNull($icon);
        $this->sortOrder = $sortOrder;
    }

    public function deactivate(): void
    {
        if (!$this->isActive) {
            throw new DomainException('Категория уже отключена.');
        }

        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function isRoot(): bool
    {
        return null === $this->parent;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public static function normalizeName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);

        if ('' === $name) {
            throw new DomainException('Название категории не может быть пустым.');
        }

        if (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            throw new DomainException(sprintf('Название категории не длиннее %d символов.', self::NAME_MAX_LENGTH));
        }

        return $name;
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
