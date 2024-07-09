<?php
namespace App\Twig;

use App\Service\CategoryService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get1stLevelCategories', [$this, 'get1stLevelCategories']),
        ];
    }

    public function get1stLevelCategories()
    {
        return $this->categoryService->get1stLevelCategories();
    }
}