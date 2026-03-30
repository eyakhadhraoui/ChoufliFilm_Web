<?php

namespace App\Entity;

use App\Repository\FilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(max: 30, maxMessage: "Le titre ne peut pas dépasser 30 caractères.")]
    private ?string $titre = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le directeur ne peut pas être vide.")]
    #[Assert\Length(max: 30, maxMessage: "Le nom du directeur ne peut pas dépasser 30 caractères.")]
    private ?string $directeur = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La note ne peut pas être vide.")]
    #[Assert\Range(
        min: 0,
        max: 10,
        notInRangeMessage: "La note doit être comprise entre {{ min }} et {{ max }}."
    )]
    private ?float $note = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le genre ne peut pas être vide.")]
    #[Assert\Length(max: 30, maxMessage: "Le genre ne peut pas dépasser 30 caractères.")]
    private ?string $genre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(max: 255, maxMessage: "La description ne peut pas dépasser 255 caractères.")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de début ne peut pas être nulle.")]
#[Assert\NotBlank(message: "La date de début ne peut pas être vide.")]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
     #[Assert\NotNull(message: "La date de début ne peut pas être nulle.")]
    #[Assert\NotBlank(message: "La date de fin ne peut pas être vide.")]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La durée ne peut pas être vide.")]
    #[Assert\Positive(message: "La durée doit être un nombre positif.")]
    private ?int $duree = null;

    /**
     * @var Collection<int, Salle>
     */
    #[ORM\ManyToMany(targetEntity: Salle::class, mappedBy: 'films')]
    private Collection $salles;

    #[ORM\Column(length: 100)]
    private ?string $image_film = null;

    public function __construct()
    {
        $this->salles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDirecteur(): ?string
    {
        return $this->directeur;
    }

    public function setDirecteur(string $directeur): static
    {
        $this->directeur = $directeur;

        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(float $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

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

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * @return Collection<int, Salle>
     */
    public function getSalles(): Collection
    {
        return $this->salles;
    }

    public function addSalle(Salle $salle): static
    {
        if (!$this->salles->contains($salle)) {
            $this->salles->add($salle);
            $salle->addFilm($this);
        }

        return $this;
    }

    public function removeSalle(Salle $salle): static
    {
        if ($this->salles->removeElement($salle)) {
            $salle->removeFilm($this);
        }

        return $this;
    }

    public function getImageFilm(): ?string
    {
        return $this->image_film;
    }

    public function setImageFilm(string $image_film): static
    {
        $this->image_film = $image_film;

        return $this;
    }
}
