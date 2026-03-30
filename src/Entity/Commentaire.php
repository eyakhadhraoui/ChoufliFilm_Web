<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;
use \DateTime; // Importation de la classe DateTime

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le contenu ne peut pas être vide.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le contenu ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $contenu_com = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date ne peut pas être vide.")]
    #[Assert\Date(message: "La date doit être au format valide.")]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "L'article associé est requis.")]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "L'utilisateur est requis.")]
    #[Assert\Valid] // Vérifie la validité de l'objet User
    private ?User $user = null;
    
    public function __construct()
    {
        // Définir la date actuelle par défaut si elle n'est pas spécifiée
        $this->date = new \DateTime(); // Date actuelle
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuCom(): ?string
    {
        return $this->contenu_com;
    }

    public function setContenuCom(string $contenu_com): static
    {
        $this->contenu_com = $contenu_com;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

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
}}
