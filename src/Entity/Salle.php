<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le nom de la salle ne peut pas être vide.")]
    #[Assert\Length(max: 30, maxMessage: "Le nom de la salle ne peut pas dépasser 30 caractères.")]
    private ?string $nom_salle = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le nombre de places ne peut pas être vide.")]
    #[Assert\Positive(message: "Le nombre de places doit être un nombre positif.")]
    #[Assert\LessThanOrEqual(value: 50, message: "Le nombre de places ne peut pas dépasser 50.")]
    private ?int $nbr_places = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le type de la salle ne peut pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "Le type de la salle ne peut pas dépasser 50 caractères.")]
    private ?string $Type_salle = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "L'état de la salle ne peut pas être vide.")]
    #[Assert\Length(max: 30, maxMessage: "L'état de la salle ne peut pas dépasser 30 caractères.")]
    private ?string $Etat_salle = null;

    /**
     * @var Collection<int, Film>
     */
    #[ORM\ManyToMany(targetEntity: Film::class, inversedBy: 'salles')]
    private Collection $films;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'salle', orphanRemoval: true)]
    private Collection $reservations;

    #[ORM\Column(length: 100)]
    private ?string $image_salle = null;

    public function __construct()
    {
        $this->films = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSalle(): ?string
    {
        return $this->nom_salle;
    }

    public function setNomSalle(string $nom_salle): static
    {
        $this->nom_salle = $nom_salle;

        return $this;
    }

    public function getNbrPlaces(): ?int
    {
        return $this->nbr_places;
    }

    public function setNbrPlaces(int $nbr_places): static
    {
        $this->nbr_places = $nbr_places;

        return $this;
    }

    public function getTypeSalle(): ?string
    {
        return $this->Type_salle;
    }

    public function setTypeSalle(string $Type_salle): static
    {
        $this->Type_salle = $Type_salle;

        return $this;
    }

    public function getEtatSalle(): ?string
    {
        return $this->Etat_salle;
    }

    public function setEtatSalle(string $Etat_salle): static
    {
        $this->Etat_salle = $Etat_salle;

        return $this;
    }

    /**
     * @return Collection<int, Film>
     */
    public function getFilms(): Collection
    {
        return $this->films;
    }

    public function addFilm(Film $film): static
    {
        if (!$this->films->contains($film)) {
            $this->films->add($film);
        }

        return $this;
    }

    public function removeFilm(Film $film): static
    {
        $this->films->removeElement($film);

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setSalle($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getSalle() === $this) {
                $reservation->setSalle(null);
            }
        }

        return $this;
    }

    public function getImageSalle(): ?string
    {
        return $this->image_salle;
    }

    public function setImageSalle(string $image_salle): static
    {
        $this->image_salle = $image_salle;

        return $this;
    }
}
