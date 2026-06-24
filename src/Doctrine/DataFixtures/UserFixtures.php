<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use function array_fill_callback;

final class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $users = array_map(fn (int $index): User => (new User())
            ->setEmail(sprintf('user+%d@email.com', $index))
            ->setPlainPassword('password')
            ->setUsername(sprintf('user+%d', $index)),
            range(0, 9)
        );

        // Utilisateur dédié aux tests fonctionnels (sans review)
        $users[] = (new User())
            ->setEmail('testeur@critipixel.fr')
            ->setPlainPassword('password')
            ->setUsername('testeur');

        array_walk($users, [$manager, 'persist']);

        $manager->flush();
    }
}
