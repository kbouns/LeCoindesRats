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
        $faker = Factory::create();

        // Create regular users
        for ($i = 0; $i < 25; $i++) {
            $user = new User();
            $user->setUsername($faker->userName);
            $user->setEmail($faker->unique()->safeEmail);
            $user->setPassword($this->passwordHasher->hashPassword($user, '1111'));
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setInscriptionTime($faker->dateTimeThisYear);
            $manager->persist($user);
        }

        // Create admin user
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@admin.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->setFirstname('Admin');
        $admin->setLastname('Rats');
        $admin->setInscriptionTime(new \DateTime());
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $manager->flush();
    }
}
