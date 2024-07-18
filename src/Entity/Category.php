<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nameCategory = null;

    /**
     * @var Collection<int, Deal>
     */
    #[ORM\ManyToMany(targetEntity: Deal::class, mappedBy: 'categorie')]
    private Collection $deals;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'categories')]
    #[ORM\JoinColumn(name: 'categories_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?self $Categories = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'Categories', cascade: ['persist', 'remove'])]
    private Collection $categories;

    public function __construct()
    {
        $this->deals = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameCategory(): ?string
    {
        return $this->nameCategory;
    }

    public function setNameCategory(string $nameCategory): static
    {
        $this->nameCategory = $nameCategory;

        return $this;
    }

    /**
     * @return Collection<int, Deal>
     */
    public function getDeals(): Collection
    {
        return $this->deals;
    }

    public function addDeal(Deal $deal): static
    {
        if (!$this->deals->contains($deal)) {
            $this->deals->add($deal);
            $deal->addCategorie($this);
        }

        return $this;
    }

    public function removeDeal(Deal $deal): static
    {
        if ($this->deals->removeElement($deal)) {
            $deal->removeCategorie($this);
        }

        return $this;
    }

    public function getCategories(): ?self
    {
        return $this->Categories;
    }

    public function setCategories(?self $Categories): static
    {
        $this->Categories = $Categories;

        return $this;
    }

    public function addCategory(self $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setCategories($this);
        }

        return $this;
    }

    public function removeCategory(self $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getCategories() === $this) {
                $category->setCategories(null);
            }
        }

        return $this;
    }
}
