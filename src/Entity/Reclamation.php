<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   
    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable('now', new \DateTimeZone('Africa/Tunis'));
    }
    

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 30)]
    private ?string $type = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "Description  est obligatoire.")]
    #[Assert\Length(min: 8, minMessage: "Votre Descripton doit contenir au moins {{ limit }} caractères.")]
    private ?string $Description = null;
   
    
   


    #[ORM\OneToOne(mappedBy: 'reclamation', cascade: ['persist', 'remove'])]
    private ?Reponse $reponse = null;
   
    #[ORM\ManyToOne(inversedBy: 'reclamation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $created_at = null;
   

    #[ORM\Column(length: 10,nullable: true)]
    private ?string $priority = null;



    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

  
    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getReponse(): ?Reponse
    {
        return $this->reponse;
    }

    public function setReponse(Reponse $reponse): static
    {
        // set the owning side of the relation if necessary
        if ($reponse->getReclamation() !== $this) {
            $reponse->setReclamation($this);
        }

        $this->reponse = $reponse;

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
    public function getReponseTexte(): ?string
{
    return $this->reponse ? $this->reponse->getReponse() : null;
}

public function setReponseTexte(?string $texte): void
{
    if ($this->reponse) {
        $this->reponse->setReponse($texte);
    } else {
        $this->reponse = new Reponse();
        $this->reponse->setReponse($texte);
    }
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
public function getUsertel(): ?string
{
    return $this->user ? $this->user->getNumTelephone() : null;
}

public function getCreatedAt(): ?\DateTimeImmutable
{
    return $this->created_at;
}

public function setCreatedAt(?\DateTimeImmutable $created_at): static
{
    $this->created_at = $created_at;

    return $this;
}



}
