<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DogRepository;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Controller\GetRandomDog;

#[ORM\Entity(repositoryClass: DogRepository::class)]
#[UniqueEntity('image')]
#[ApiResource(operations: [
    new Get(),
    new Post(),
    new Put(),
    new Delete(),
    new GetCollection(
        name: 'get_all_dogs',
        uriTemplate: '/dogs',
        controller: null,
        paginationEnabled: true
    ),
    new Get(
        name: 'random_dog',
        uriTemplate: '/dogs/random',
        controller: GetRandomDog::class,
        paginationEnabled: false
    )
])]
class Dog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $breed = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subBreed = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBreed(): ?string
    {
        return $this->breed;
    }

    public function setBreed(string $breed): self
    {
        $this->breed = $breed;
        return $this;
    }

    public function getSubBreed(): ?string
    {
        return $this->subBreed;
    }

    public function setSubBreed(?string $subBreed): self
    {
        $this->subBreed = $subBreed;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }
    
    public function __toString(): string
    {
        return $this->breed;
    }
}
