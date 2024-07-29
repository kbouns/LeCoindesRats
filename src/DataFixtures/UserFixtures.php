<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création des utilisateurs réguliers
        for ($i = 0; $i < 25; $i++) {
            $email = $faker->unique()->safeEmail;
            $username = $faker->unique()->userName;

            // Vérifier si l'utilisateur existe déjà par email ou par username
            $existingUser = $manager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$existingUser) {
                $user = new User();
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setPassword($this->passwordHasher->hashPassword($user, '1111'));
                $user->setFirstname($faker->firstName);
                $user->setLastname($faker->lastName);
                $user->setInscriptionTime($faker->dateTimeThisYear);
                $user->setRoles(['ROLE_USER']);
                $manager->persist($user);
            }
        }

        // Création de l'utilisateur admin
        $adminEmail = 'admin@admin.com';
        $existingAdmin = $manager->getRepository(User::class)->findOneBy(['email' => $adminEmail]);

        if (!$existingAdmin) {
            $admin = new User();
            $admin->setUsername('admin');
            $admin->setEmail($adminEmail);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
            $admin->setFirstname('Admin');
            $admin->setLastname('Rats');
            $admin->setInscriptionTime(new \DateTime());
            $admin->setRoles(['ROLE_ADMIN']);
            $manager->persist($admin);
        }

        // Création de l'utilisateur modérateur
        $moderatorEmail = 'moderator@example.com';
        $existingModerator = $manager->getRepository(User::class)->findOneBy(['email' => $moderatorEmail]);

        if (!$existingModerator) {
            $moderator = new User();
            $moderator->setUsername('moderator');
            $moderator->setEmail($moderatorEmail);
            $moderator->setPassword($this->passwordHasher->hashPassword($moderator, 'mod11'));
            $moderator->setFirstname('Moderator');
            $moderator->setLastname('Erator');
            $moderator->setInscriptionTime(new \DateTime());
            $moderator->setRoles(['ROLE_MODERATOR']);
            $manager->persist($moderator);
        }

        $manager->flush();
    }
}
