<?php

namespace App\DataFixtures;

use App\Entity\Vote;
use App\Entity\Deal;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class VoteFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $dealRepository = $manager->getRepository(Deal::class);
        $userRepository = $manager->getRepository(User::class);

        $deals = $dealRepository->findAll();
        $users = $userRepository->findAll();

        foreach ($deals as $deal) {
            foreach ($users as $user) {
                // Vérifier si le vote existe déjà pour cet utilisateur et ce deal
                $existingVote = $manager->getRepository(Vote::class)->findOneBy([
                    'user' => $user,
                    'deal' => $deal
                ]);

                if (!$existingVote) {
                    // Générer un vote aléatoire
                    $voteType = rand(0, 1) ? 'upvote' : 'downvote';
                    
                    $vote = new Vote();
                    $vote->setUser($user);
                    $vote->setDeal($deal);
                    $vote->setTypeVote($voteType);
                    $manager->persist($vote);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            AppFixtures::class,
            UserFixtures::class,
        ];
    }
}
