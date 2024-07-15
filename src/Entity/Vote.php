<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\UniqueConstraint(columns: ['user_id', 'deal_id'])]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $typeVote = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    private ?Deal $deal = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeVote(): ?string
    {
        return $this->typeVote;
    }

    public function setTypeVote(string $typeVote): static
    {
        $this->typeVote = $typeVote;

        return $this;
    }

    public function getDeal(): ?Deal
    {
        return $this->deal;
    }

    public function setDeal(?Deal $deal): static
    {
        $this->deal = $deal;

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
}