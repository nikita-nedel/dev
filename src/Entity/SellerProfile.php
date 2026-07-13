<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SellerProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SellerProfileRepository::class)]
#[ORM\Table(name: 'seller_profile')]
class SellerProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'profile', targetEntity: Seller::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Seller $seller = null;

    #[ORM\Column(length: 255)]
    private ?string $shopName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legalName = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $inn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactPhone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(Seller $seller): static
    {
        $this->seller = $seller;

        return $this;
    }

    public function getShopName(): ?string
    {
        return $this->shopName;
    }

    public function setShopName(string $shopName): static
    {
        $this->shopName = $shopName;

        return $this;
    }

    public function getLegalName(): ?string
    {
        return $this->legalName;
    }

    public function setLegalName(?string $legalName): static
    {
        $this->legalName = $legalName;

        return $this;
    }

    public function getInn(): ?string
    {
        return $this->inn;
    }

    public function setInn(?string $inn): static
    {
        $this->inn = $inn;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }
}
