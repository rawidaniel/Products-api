<?php

namespace App\Entity;

use App\Entity\Manufacturer;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProductRepository;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;



/** A Product */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    uriTemplate: '/manufacturer/{id}/products',
    uriVariables: [
        'id' => new Link(
            fromClass: Manufacturer::class,
            fromProperty: 'products'
        )
    ],
    operations: [
        new GetCollection()
    ]
)]
#[ApiResource(
    security: "is_granted('ROLE_USER')",
    paginationItemsPerPage: 2,
    normalizationContext: ['groups' => ['product.read']],
    denormalizationContext: ['groups' => ['product.write']]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'name' => SearchFilter::STRATEGY_PARTIAL,
        'description' => SearchFilter::STRATEGY_PARTIAL,
        'manufacturer.countryCode' => SearchFilter::STRATEGY_EXACT,
        'manufacturer.id' => SearchFilter::STRATEGY_EXACT

    ]
)]
#[ApiFilter(
    OrderFilter::class,
    properties: ['issueDate'],
)]

class Product
{
    /** The ID of the product */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product.read'])]
    private ?int $id = null;

    /** The MPN (manufacturer part number) of the product. */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotNull]
    #[Groups(['product.read', 'product.write'])]
    private ?string $mpn = null;

    /** The name  of the product. */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['product.read', 'product.write'])]
    private ?string $name = null;

    /** The description  of the product. */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['product.read', 'product.write'])]
    private ?string $description = null;

    /** The date of issue of the product. */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull]
    #[Groups(['product.read', 'product.write'])]
    private ?\DateTimeInterface $issueDate = null;

    #[ORM\ManyToOne(inversedBy: 'products', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['product.read', 'product.write'])]
    private ?Manufacturer $manufacturer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn(?string $mpn): static
    {
        $this->mpn = $mpn;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getIssueDate(): ?\DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(\DateTimeInterface $issueDate): static
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }
}
