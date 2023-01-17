<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $pwd = 'test';

        for ($i=0; $i<10; $i++) {
            $user = (new User())
                ->setEmail($i . 'user@user.fr')
                ->setPlainPassword($pwd)
                ->setRoles(['ROLE_USER'])
            ;
            $manager->persist($user);
        }

        $user = (new User())
            ->setEmail('user@user.fr')
            ->setPlainPassword($pwd)
            ->setRoles(['ROLE_USER'])
        ;
        $manager->persist($user);

        $user = (new User())
            ->setEmail('admin@user.fr')
            ->setPlainPassword($pwd)
            ->setRoles(['ROLE_ADMIN'])
        ;
        $manager->persist($user);

        $user = (new User())
            ->setEmail('validator@user.fr')
            ->setPlainPassword($pwd)
            ->setRoles(['ROLE_VALIDATOR'])
        ;
        $manager->persist($user);

        $manager->flush();
    }
}
