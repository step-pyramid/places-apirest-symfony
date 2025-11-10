<?php
// src/Entity/Place.php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
#[ORM\Table(name: 'places')]
class Place
{
    // Valid categories - preserved from your original
    const CATEGORIES = [
        'cafe', 'restaurant', 'museum', 'park', 'landmark', 
        'shop', 'hotel', 'theater', 'bar', 'bakery', 'library',
        'gallery', 'monument', 'mall', 'market'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(max: 255, maxMessage: 'Name must be less than 255 characters')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Category is required')]
    #[Assert\Length(max: 50, maxMessage: 'Category must be less than 50 characters')]
    #[Assert\Choice(choices: self::CATEGORIES, message: 'Category must be one of: {{ choices }}')]
    private ?string $category = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Address is required')]
    private ?string $address = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'City is required')]
    #[Assert\Length(max: 100, maxMessage: 'City must be less than 100 characters')]
    private ?string $city = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 5,
        notInRangeMessage: 'Rating must be between {{ min }} and {{ max }}'
    )]
    private ?string $rating = null;

    #[ORM\Column(length: 100)]
    private ?string $submitted_by = 'anonymous';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    // Constructor - adapted for Symfony
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->category = $data['category'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->rating = isset($data['rating']) ? (string) floatval($data['rating']) : null;
        $this->submitted_by = $data['submitted_by'] ?? 'anonymous';
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        
        // Set timestamps if not provided
        if ($this->created_at === null) {
            $this->created_at = new \DateTime();
        }
        if ($this->updated_at === null) {
            $this->updated_at = new \DateTime();
        }
    }

    // Getters and Setters
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

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    public function getSubmittedBy(): ?string
    {
        return $this->submitted_by;
    }

    public function setSubmittedBy(string $submitted_by): static
    {
        $this->submitted_by = $submitted_by;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    // Preserved methods from your original entity
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'address' => $this->address,
            'city' => $this->city,
            'rating' => $this->rating,
            'submitted_by' => $this->submitted_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s')
        ];
    }

    public function isValid(): bool
    {
        return empty($this->getValidationErrors());
    }

    public function getValidationErrors(): array
    {
        $errors = [];
        
        // Symfony validation handles most cases, but we keep this for custom logic
        if (empty(trim($this->name ?? ''))) {
            $errors[] = 'Name is required';
        }
        
        if (empty(trim($this->category ?? ''))) {
            $errors[] = 'Category is required';
        }
        
        if (empty(trim($this->address ?? ''))) {
            $errors[] = 'Address is required';
        }
        
        if (empty(trim($this->city ?? ''))) {
            $errors[] = 'City is required';
        }
        
        return $errors;
    }

    // Helper methods - preserved from your original
    public function hasRating(): bool
    {
        return $this->rating !== null;
    }

    public function getFormattedRating(): string
    {
        return $this->rating !== null ? number_format((float) $this->rating, 1) : 'Not rated';
    }

    public function getFullAddress(): string
    {
        return $this->address . ', ' . $this->city;
    }
}