<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $commenttime = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?User $User = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Deal $Deal = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'comments')]
    private ?self $Commentaire = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'Commentaire')]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCommenttime(): ?\DateTimeInterface
    {
        return $this->commenttime;
    }

    public function setCommenttime(\DateTimeInterface $commenttime): static
    {
        $this->commenttime = $commenttime;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getDeal(): ?Deal
    {
        return $this->Deal;
    }

    public function setDeal(?Deal $Deal): static
    {
        $this->Deal = $Deal;

        return $this;
    }

    public function getCommentaire(): ?self
    {
        return $this->Commentaire;
    }

    public function setCommentaire(?self $Commentaire): static
    {
        $this->Commentaire = $Commentaire;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(self $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setCommentaire($this);
        }

        return $this;
    }

    public function removeComment(self $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCommentaire() === $this) {
                $comment->setCommentaire(null);
            }
        }

        return $this;
    }
}
