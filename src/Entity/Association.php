<?php

namespace App\Entity;

use App\Repository\AssociationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssociationRepository::class)]
class Association
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "nom est Obligatoire")]
    #[Assert\Length(min: 10, minMessage: "Votre nom ne contient pas {{ limit }} caractères .")]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(
        message: "L'email '{{ value }}' n'est pas valide.",
        mode: "strict"
    )]
    #[Assert\Regex(
        pattern: "/@(gmail\.com|esprit\.tn)$/",
        message: "L'email doit se terminer par @gmail.com ou @esprit.tn"
    )]
   
    private ?string $mail_association = null;


   
   



    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'adresse ne peut pas être vide.")]
    #[Assert\Length(min: 5, minMessage: "Votre adresse doit contenir au moins {{ limit }} caractères.")]
    private ?string $adresse = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le numéro de téléphone est obligatoire.")]
    #[Assert\Positive(message: "Le numéro de téléphone doit être un nombre positif.")]
    #[Assert\Length(
        min: 8,
        max: 8,
        exactMessage: "Le numéro de téléphone doit contenir exactement {{ limit }} chiffres."
    )]
    #[Assert\Type(
        type: "integer",
        message: "Le numéro de téléphone doit contenir uniquement des chiffres."
    )]
    public ?int $num_tel = null;

    #[ORM\Column(length: 100)]
    private ?string $image = null;

    #[ORM\Column(length: 254)]
    #[Assert\NotBlank(message: "L'adresse ne peut pas être vide.")]
    #[Assert\Length(min: 5, minMessage: "Votre adresse doit contenir au moins {{ limit }} caractères.")]
    private ?string $Description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getMailAssociation(): ?string
    {
        return $this->mail_association;
    }

    public function setMailAssociation(string $mail_association): static
    {
        $this->mail_association = $mail_association;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getnumero_telephone(): ?int
    {
        return $this->num_tel;
    }

    public function setnumero_telephone(int $numero_telephone): static
    {
        $this->num_tel = $numero_telephone;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

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
}
