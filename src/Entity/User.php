<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PhpParser\Node\Expr\Array_;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements PasswordAuthenticatedUserInterface , UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "nom est Obligatoire")]
    #[Assert\Length(min: 3,
    max:20 ,
   exactMessage: "Le Nom doit contenir exactement {{ limit }} chiffres.")]
    private ?string $nom = '';

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le Prenom est obligatoire.")]
    #[Assert\Length(min: 3,
    max:20 ,
   exactMessage: "Le Prénom doit contenir exactement {{ limit }} chiffres.")]
    private ?string $prenom = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(
        message: "L'email '{{ value }}' n'est pas valide.",
        mode: "strict"
    )]
    #[Assert\Regex(
        pattern: "/@(gmail\.com|esprit\.tn)$/",
        message: "L'email doit se terminer par @gmail.com ou @esprit.tn"
    )]
    private ?string $email = null;

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
    private ?int $num_telephone = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date est obligatoire")]
    #[Assert\LessThanOrEqual(
        value: "today - 10 years",
        message: "Vous devez avoir au moins 10 ans."
    )]
    #[Assert\Type(
        type: "\DateTimeInterface",
        message: "Le champ doit être une date valide."
    )]
    private ?\DateTimeInterface $date_naissance = null;

    #[ORM\Column(length: 30)]
    private ?string $localisation = null;

    #[ORM\Column(length: 30 , nullable: true)]
    private ?string $bannedAt= null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(type: 'json')]
    #[Assert\NotBlank(message: "Role est obligatoire.")]
    private array $roles;

    #[ORM\Column()]
    #[Assert\NotBlank(message: "Password est obligatoire.")]
    private ?string $password = null;

    #[ORM\Column]
    private ?int $banned = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $banned_until = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $googleId;

    #[ORM\Column]
    private ?int $deleted = null;

    #[ORM\Column]
    private ?int $nblogin = 0;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $verification_code = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Password est obligatoire.")]
    private ?string $confirm_password = null;

    /**
     * @var Collection<int, Reclamation>
     */
    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $reclamation;

    /**
     * @var Collection<int, Ressource>
     */
    #[ORM\OneToMany(targetEntity: Ressource::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $ressources;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $reservations;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $commentaires;

   

    public function __construct()
    {
        $this->reclamation = new ArrayCollection();
        $this->ressources = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }


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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNumTelephone(): ?int
    {
        return $this->num_telephone;
    }

    public function setNumTelephone(int $num_telephone): static
    {
        $this->num_telephone = $num_telephone;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(\DateTimeInterface $date_naissance): static
    {
        $this->date_naissance = $date_naissance;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getBanned(): ?int
    {
        return $this->banned;
    }

    public function setBanned(int $banned): static
    {
        $this->banned = $banned;

        return $this;
    }
    
    public function getBannedAt(): ?string
    {
        return $this->bannedAt;
    }

    public function setBannedAt(string $banned): static
    {
        $this->bannedAt = $banned;

        return $this;
    }

    public function getBannedUntil(): ?\DateTimeInterface
    {
        return $this->banned_until;
    }

    public function setBannedUntil(?\DateTimeInterface $banned_until): static
    {
        $this->banned_until = $banned_until;

        return $this;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(int $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }




    public function getnblogin(): ?int
    {
        return $this->nblogin;
    }

    public function setnblogin(int $nblogin): static
    {
        $this->nblogin = $nblogin;

        return $this;
    }


    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;
        return $this;
    }

    public function getVerificationCode(): ?string
    {
        return $this->verification_code;
    }

    public function setVerificationCode(?string $verification_code): static
    {
        $this->verification_code = $verification_code;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $confirm_password): static
    {
        $this->confirm_password = $confirm_password;

        return $this;
    }

    /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamation(): Collection
    {
        return $this->reclamation;
    }

    public function addReclamation(Reclamation $reclamation): static
    {
        if (!$this->reclamation->contains($reclamation)) {
            $this->reclamation->add($reclamation);
            $reclamation->setUser($this);
        }

        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): static
    {
        if ($this->reclamation->removeElement($reclamation)) {
            // set the owning side to null (unless already changed)
            if ($reclamation->getUser() === $this) {
                $reclamation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ressource>
     */
    public function getRessources(): Collection
    {
        return $this->ressources;
    }

    public function addRessource(Ressource $ressource): static
    {
        if (!$this->ressources->contains($ressource)) {
            $this->ressources->add($ressource);
            $ressource->setUser($this);
        }

        return $this;
    }

    public function removeRessource(Ressource $ressource): static
    {
        if ($this->ressources->removeElement($ressource)) {
            // set the owning side to null (unless already changed)
            if ($ressource->getUser() === $this) {
                $ressource->setUser(null);
            }
        }

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
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setUser($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUser() === $this) {
                $commentaire->setUser(null);
            }
        }

        return $this;
    }
}