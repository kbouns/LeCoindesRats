<?php
namespace App\Service;

use App\Repository\CategoryRepository;

class CategoryService
{
    private $repo;

    public function __construct(CategoryRepository $repo){
        $this->repo = $repo;
    }

    public function get1stLevelCategories()
    {
        return $this->repo->findBy(['Categories' => null]);
    }
}