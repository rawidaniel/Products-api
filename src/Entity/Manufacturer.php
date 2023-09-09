<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\ApiSubresource;
use App\Repository\ManufacturerRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/** A Manufacturer */
#[GET()]
#[ORM\Entity(repositoryClass: ManufacturerRepository::class)]
#[ApiResource(
    uriTemplate: '/products/{id}/manufacturer',
    uriVariables: [
        'id' => new Link(fromClass: Product::class, fromProperty: 'manufacturer'),
    ],
    operations: [
        new Get()
    ]
)]
#[ApiResource(
    // routePrefix: '/country',
    normalizationContext: ['groups' => ['manufacturer.read']],
    denormalizationContext: ['groups' => ['manufacturer.write']],
    security: "is_granted('ROLE_USER')",
    operations:[
        // new Get(
            
        // ),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete()
        ],
       

)]
class Manufacturer
{
    /** The ID of the manufacturer. */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['manufacturer.read'])]
    private ?int $id = null;

    /** The name of the manufacturer. */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    // #[Groups(['product.read'])]
    #[Groups(['manufacturer.read', 'manufacturer.write'])]
    private ?string $name = null;

    /** The description of the manufacturer. */
    #[ORM\Column(length: 500)]
    #[Assert\NotBlank]
    #[Groups(['manufacturer.read', 'manufacturer.write'])]
    private ?string $description = null;

    /** The country code of the manufacturer. */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['manufacturer.read', 'manufacturer.write'])]
    private ?string $countryCode = null;

    /** The listed date of the manufacturer. */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull]
    #[Groups(['manufacturer.read', 'manufacturer.write'])]
    private ?\DateTimeInterface $listedDate = null;

    #[ORM\OneToMany(mappedBy: 'manufacturer', targetEntity: Product::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    // #[Link(fromProperty: 'products')
    #[Groups(['manufacturer.read'])]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getListedDate(): ?\DateTimeInterface
    {
        return $this->listedDate;
    }

    public function setListedDate(\DateTimeInterface $listedDate): static
    {
        $this->listedDate = $listedDate;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setManufacturer($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getManufacturer() === $this) {
                $product->setManufacturer(null);
            }
        }

        return $this;
    }
}
