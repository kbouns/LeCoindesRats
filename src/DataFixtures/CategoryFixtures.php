<?php
// src/DataFixtures/CategoryFixtures.php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'High-Tech',
            'Consoles & Jeux vidéo',
            'Épicerie & Courses',
            'Mode & Accessoires',
            'Santé & Cosmétiques',
            'Famille & Enfants',
            'Maison & Habitat',
            'Jardin & Bricolage',
            'Auto-Moto',
            'Culture & Divertissement',
            'Sports & Plein air',
            'Services',
            'Voyages',
        ];

        foreach ($categories as $name) {
            $category = new Category();
            $category->setNameCategory($name); // Utilisez setNameCategory ici
            $manager->persist($category);
        }

        $manager->flush();
    }
}
