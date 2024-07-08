<?php

namespace App\Entity;

use App\Repository\DealRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DealRepository::class)]
class Deal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $initialprice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $reduceprice = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $publicationdate = null;

    #[ORM\Column(length: 1024)]
    private ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'deals')]
    private ?User $User = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'Deal')]
    private Collection $comments;

    #[ORM\Column(length: 255)]
    private ?string $Delivery = null;

    #[ORM\Column(length: 255)]
    private ?string $ImageFilename = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'Deal')]
    private Collection $votes;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'deals')]
    private Collection $Categorie;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->Categorie = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getInitialprice(): ?string
    {
        return $this->initialprice;
    }

    public function setInitialprice(string $initialprice): static
    {
        $this->initialprice = $initialprice;

        return $this;
    }

    public function getReduceprice(): ?string
    {
        return $this->reduceprice;
    }

    public function setReduceprice(string $reduceprice): static
    {
        $this->reduceprice = $reduceprice;

        return $this;
    }

    public function getPublicationdate(): ?\DateTimeInterface
    {
        return $this->publicationdate;
    }

    public function setPublicationdate(\DateTimeInterface $publicationdate): static
    {
        $this->publicationdate = $publicationdate;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->idUser;
    }

    public function setIdUser(?User $idUser): static
    {
        $this->idUser = $idUser;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setDeal($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getDeal() === $this) {
                $comment->setDeal(null);
            }
        }

        return $this;
    }

    public function getDelivery(): ?string
    {
        return $this->Delivery;
    }

    public function setDelivery(string $Delivery): static
    {
        $this->Delivery = $Delivery;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->ImageFilename;
    }

    public function setImageFilename(string $ImageFilename): static
    {
        $this->ImageFilename = $ImageFilename;

        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setDeal($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getDeal() === $this) {
                $vote->setDeal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategorie(): Collection
    {
        return $this->Categorie;
    }

    public function addCategorie(Category $categorie): static
    {
        if (!$this->Categorie->contains($categorie)) {
            $this->Categorie->add($categorie);
        }

        return $this;
    }

    public function removeCategorie(Category $categorie): static
    {
        $this->Categorie->removeElement($categorie);

        return $this;
    }
}
