<?php

namespace App\DataFixtures;

use App\Entity\Deal;
use App\Entity\User;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Utiliser le local 'fr_FR' pour générer des données en français

        // Récupérer tous les utilisateurs et toutes les catégories
        $users = $manager->getRepository(User::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();

        // Récupérer la liste des fichiers images dans le répertoire uploads/images
        $imageDirectory = 'public/uploads/images';
        $images = array_diff(scandir($imageDirectory), ['.', '..']); // Récupérer tous les fichiers, sauf . et ..

        for ($i = 0; $i < 35; $i++) {
            $deal = new Deal();
            $deal->setTitle($faker->sentence(3)); // Générer un titre en français
            $deal->setDescription($faker->paragraph); // Générer une description en français
            $deal->setInitialprice($faker->numberBetween(50, 100));
            $deal->setReduceprice($faker->numberBetween(10, 49));
            $deal->setPublicationdate($faker->dateTimeThisYear);
            $deal->setLink($faker->url);

            // Sélectionner un utilisateur aléatoire
            $randomUser = $users[array_rand($users)];
            $deal->setUser($randomUser);

            // Ajouter plusieurs catégories aléatoires
            $numberOfCategories = $faker->numberBetween(1, min(5, count($categories))); // Choisir un nombre aléatoire de catégories
            $randomCategories = $faker->randomElements($categories, $numberOfCategories);
            foreach ($randomCategories as $category) {
                $deal->addCategorie($category);
            }

            // Ajouter aléatoirement 'gratuite' ou 'payante' pour la livraison
            $deliveryOptions = ['gratuite', 'payante'];
            $randomDelivery = $deliveryOptions[array_rand($deliveryOptions)];
            $deal->setDelivery($randomDelivery);

            // Sélectionner une image aléatoire du répertoire uploads/images
            if (!empty($images)) {
                $randomImage = $images[array_rand($images)];
                $deal->setImageFilename('' . $randomImage); // Utiliser l'image existante
            } else {
                $deal->setImageFilename(null); // Aucun fichier image trouvé
            }

            $deal->setIsActive(true);

            $manager->persist($deal);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}