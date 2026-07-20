<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SellerRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: SellerRepository::class)]
#[ORM\Table(name: 'seller')]
#[ORM\UniqueConstraint(name: 'UNIQ_SELLER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_SELLER_CUSTOMER', fields: ['customer'])]
#[UniqueEntity(fields: ['email'], message: 'Этот email уже используется')]
class Seller implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string>
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isActive = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $activationToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $activationTokenExpiresAt = null;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Customer $customer = null;

    #[ORM\OneToOne(mappedBy: 'seller', targetEntity: SellerProfile::class, cascade: ['persist', 'remove'])]
    private ?SellerProfile $profile = null;

    #[ORM\OneToOne(mappedBy: 'seller', targetEntity: SellerRequest::class, cascade: ['persist', 'remove'])]
    private ?SellerRequest $request = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_SELLER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password ?? '');

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeImmutable $approvedAt): static
    {
        $this->approvedAt = $approvedAt;

        return $this;
    }

    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    public function setActivationToken(?string $activationToken): static
    {
        $this->activationToken = $activationToken;

        return $this;
    }

    public function getActivationTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->activationTokenExpiresAt;
    }

    public function setActivationTokenExpiresAt(?\DateTimeImmutable $activationTokenExpiresAt): static
    {
        $this->activationTokenExpiresAt = $activationTokenExpiresAt;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getProfile(): ?SellerProfile
    {
        return $this->profile;
    }

    public function setProfile(SellerProfile $profile): static
    {
        if ($profile->getSeller() !== $this) {
            $profile->setSeller($this);
        }

        $this->profile = $profile;

        return $this;
    }

    public function getRequest(): ?SellerRequest
    {
        return $this->request;
    }

    public function setRequest(SellerRequest $request): static
    {
        if ($request->getSeller() !== $this) {
            $request->setSeller($this);
        }

        $this->request = $request;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isActivationTokenValid(string $token): bool
    {
        if (!$this->activationToken || !$this->activationTokenExpiresAt) {
            return false;
        }

        return hash_equals($this->activationToken, $token)
            && $this->activationTokenExpiresAt >= new \DateTimeImmutable();
    }
}
