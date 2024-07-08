<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $TypeVote = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    private ?Deal $Deal = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    private ?User $User = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isTypeVote(): ?bool
    {
        return $this->TypeVote;
    }

    public function setTypeVote(bool $TypeVote): static
    {
        $this->TypeVote = $TypeVote;

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

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }
}
