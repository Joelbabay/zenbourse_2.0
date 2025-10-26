<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
//#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée.')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastConnexion = null;

    #[ORM\Column]
    private ?bool $isInvestisseur = false;

    #[ORM\Column]
    private ?bool $isIntraday = false;

    #[ORM\Column(nullable: true)]
    //dfault false
    private ?bool $downloadRequestSubmitted = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isInterestedInInvestorMethod = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $investorAccessDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $intradayAccessDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $note = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $civility = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isInterestedInIntradayMethode = false;

    #[ORM\Column(nullable: true)]
    private ?bool $hasTemporaryInvestorAccess = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $temporaryInvestorAccessStart;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        if ($this->isInvestisseur) {
            $roles[] = 'ROLE_INVESTISSEUR';
        }

        if ($this->isIntraday) {
            $roles[] = 'ROLE_INTRADAY';
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFullName(): string
    {
        return trim(sprintf('%s %s', $this->firstname, $this->lastname));
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLastConnexion(): ?\DateTimeInterface
    {
        return $this->lastConnexion;
    }

    public function setLastConnexion(?\DateTimeInterface $lastConnexion): static
    {
        $this->lastConnexion = $lastConnexion;

        return $this;
    }

    public function isInvestisseur(): ?bool
    {
        return $this->isInvestisseur;
    }

    public function setIsInvestisseur(bool $isInvestisseur): static
    {
        $this->isInvestisseur = $isInvestisseur;

        return $this;
    }

    public function isIntraday(): ?bool
    {
        return $this->isIntraday;
    }

    public function setIsIntraday(bool $isIntraday): static
    {
        $this->isIntraday = $isIntraday;

        return $this;
    }

    public function isDownloadRequestSubmitted(): ?bool
    {
        return $this->downloadRequestSubmitted;
    }

    public function setIsDownloadRequestSubmitted(?bool $downloadRequestSubmitted): static
    {
        $this->downloadRequestSubmitted = $downloadRequestSubmitted;

        return $this;
    }

    public function isInterestedInInvestorMethod(): ?bool
    {
        return $this->isInterestedInInvestorMethod;
    }

    public function setInterestedInInvestorMethod(?bool $isInterestedInInvestorMethod): static
    {
        $this->isInterestedInInvestorMethod = $isInterestedInInvestorMethod;

        return $this;
    }

    public function getInvestorAccessDate(): ?\DateTimeInterface
    {
        return $this->investorAccessDate;
    }

    public function setInvestorAccessDate(?\DateTimeInterface $investorAccessDate): static
    {
        $this->investorAccessDate = $investorAccessDate;

        return $this;
    }

    public function getIntradayAccessDate(): ?\DateTimeInterface
    {
        return $this->intradayAccessDate;
    }

    public function setIntradayAccessDate(?\DateTimeInterface $intradayAccessDate): static
    {
        $this->intradayAccessDate = $intradayAccessDate;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): static
    {
        if ($note >= 1 && $note <= 5) {
            $this->note = $note;
        }

        $this->note = $note;

        return $this;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(string $civility): static
    {
        $this->civility = $civility;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function isInterestedInIntradayMethode(): ?bool
    {
        return $this->isInterestedInIntradayMethode;
    }

    public function setInterestedInIntradayMethode(?bool $isInterestedInIntradayMethode): static
    {
        $this->isInterestedInIntradayMethode = $isInterestedInIntradayMethode;

        return $this;
    }

    public function getTemporaryInvestorAccessStart(): ?\DateTimeInterface
    {
        return $this->temporaryInvestorAccessStart;
    }

    public function setTemporaryInvestorAccessStart(?\DateTimeInterface $temporaryInvestorAccessStart): static
    {
        $this->temporaryInvestorAccessStart = $temporaryInvestorAccessStart;

        return $this;
    }

    public function getHasTemporaryInvestorAccess(): ?bool
    {
        return $this->hasTemporaryInvestorAccess;
    }

    public function setHasTemporaryInvestorAccess(?bool $hasTemporaryInvestorAccess): static
    {
        $this->hasTemporaryInvestorAccess = $hasTemporaryInvestorAccess;

        return $this;
    }

    public function hasValidTemporaryInvestorAccess(): bool
    {
        if (!$this->hasTemporaryInvestorAccess || !$this->temporaryInvestorAccessStart) {
            return false;
        }
        $now = new \DateTime();
        $startDate = new \DateTime($this->temporaryInvestorAccessStart->format('Y-m-d H:i:s'));
        $end = $startDate->add(new \DateInterval('P10D'));
        if ($now <= $end) {
            return true;
        }
        $this->hasTemporaryInvestorAccess = false;
        return false;
    }

    public function getBadgeTemporaryInvestorAccess(): ?string
    {
        return null;
    }
}
