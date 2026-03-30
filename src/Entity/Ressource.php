<?php

namespace App\Entity;

use App\Repository\RessourceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;  

#[ORM\Entity(repositoryClass: RessourceRepository::class)]
class Ressource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "le champ Besoin Spécique est vide")]
    private ?string $besoin_specifique = null;

    #[ORM\ManyToOne(inversedBy: 'ressources')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Association $association = null;

    #[ORM\ManyToOne(inversedBy: 'ressources')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

  
    public function getBesoinSpecifique(): ?string
    {
        return $this->besoin_specifique;
    }

    public function setBesoinSpecifique(string $besoin_specifique): static
    {
        $this->besoin_specifique = $besoin_specifique;

        return $this;
    }

    public function getAssociation(): ?Association
    {
        return $this->association;
    }

    public function setAssociation(?Association $association): static
    {
        $this->association = $association;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
    public function getUserNom(): ?string
{
    return $this->user ? $this->user->getNom() : null;
}
public function getUserPrenom(): ?string
{
    return $this->user ? $this->user->getPrenom() : null;
}
public function getUserEmail(): ?string
{
    return $this->user ? $this->user->getEmail() : null;
}
}
