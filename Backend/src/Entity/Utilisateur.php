<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateurs')]
#[ORM\UniqueConstraint(name: 'UNIQ_UTILISATEURS_EMAIL', fields: ['email'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_utilisateur')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_utilisateur', length: 50)]
    private ?string $nom = null;

    #[ORM\Column(name: 'prenom_utilisateur', length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(name: 'email_utilisateur', length: 255)]
    private ?string $email = null;

    #[ORM\Column(name: 'mot_de_passe', length: 255)]
    private ?string $motDePasse = null;

    #[ORM\Column(name: 'role_utilisateur', length: 50)]
    private ?string $role = null;

    #[ORM\Column(name: 'email_confirme', options: ['default' => false])]
    private bool $emailConfirme = false;

    #[ORM\Column(name: 'date_inscription', type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dateInscription = null;

    /** @var Collection<int, Salle> */
    #[ORM\ManyToMany(targetEntity: Salle::class)]
    #[ORM\JoinTable(name: 'favoris_salles')]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'id_salle', referencedColumnName: 'id_salle', onDelete: 'CASCADE')]
    private Collection $sallesFavorites;

    /** @var Collection<int, Coach> */
    #[ORM\ManyToMany(targetEntity: Coach::class)]
    #[ORM\JoinTable(name: 'favoris_coachs')]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'id_coach', referencedColumnName: 'id_coach', onDelete: 'CASCADE')]
    private Collection $coachsFavoris;

    /** @var Collection<int, Cours> */
    #[ORM\ManyToMany(targetEntity: Cours::class)]
    #[ORM\JoinTable(name: 'favoris_cours')]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'id_cours', referencedColumnName: 'id_cours', onDelete: 'CASCADE')]
    private Collection $coursFavoris;

    public function __construct()
    {
        $this->dateInscription = new \DateTimeImmutable();
        $this->sallesFavorites = new ArrayCollection();
        $this->coachsFavoris = new ArrayCollection();
        $this->coursFavoris = new ArrayCollection();
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->role ? [$this->role] : ['ROLE_USER'];
    }

    public function isEmailConfirme(): bool
    {
        return $this->emailConfirme;
    }

    public function setEmailConfirme(bool $emailConfirme): static
    {
        $this->emailConfirme = $emailConfirme;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeImmutable $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    /** @return Collection<int, Salle> */
    public function getSallesFavorites(): Collection
    {
        return $this->sallesFavorites;
    }

    public function ajouterSalleFavorite(Salle $salle): static
    {
        if (!$this->sallesFavorites->contains($salle)) {
            $this->sallesFavorites->add($salle);
        }

        return $this;
    }

    public function retirerSalleFavorite(Salle $salle): static
    {
        $this->sallesFavorites->removeElement($salle);

        return $this;
    }

    /** @return Collection<int, Coach> */
    public function getCoachsFavoris(): Collection
    {
        return $this->coachsFavoris;
    }

    public function ajouterCoachFavori(Coach $coach): static
    {
        if (!$this->coachsFavoris->contains($coach)) {
            $this->coachsFavoris->add($coach);
        }

        return $this;
    }

    public function retirerCoachFavori(Coach $coach): static
    {
        $this->coachsFavoris->removeElement($coach);

        return $this;
    }

    /** @return Collection<int, Cours> */
    public function getCoursFavoris(): Collection
    {
        return $this->coursFavoris;
    }

    public function ajouterCoursFavori(Cours $cours): static
    {
        if (!$this->coursFavoris->contains($cours)) {
            $this->coursFavoris->add($cours);
        }

        return $this;
    }

    public function retirerCoursFavori(Cours $cours): static
    {
        $this->coursFavoris->removeElement($cours);

        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0motDePasse"] = hash('crc32c', (string) $this->motDePasse);

        unset(
            $data["\0" . self::class . "\0sallesFavorites"],
            $data["\0" . self::class . "\0coachsFavoris"],
            $data["\0" . self::class . "\0coursFavoris"]
        );

        return $data;
    }
}