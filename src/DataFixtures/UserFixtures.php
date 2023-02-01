<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $pwd = 'Test1234';

        for ($i=0; $i<10; $i++) {
            $user = (new User())
                ->setEmail($faker->email())
                ->setNickname($faker->userName())
                ->setDescription('je suis un agent')
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setValidationToken('ooo')
                ->setPlainPassword($pwd)
                ->setRoles(['ROLE_AGENT']);
            $manager->persist($user);
        }

        for ($i=0; $i<10; $i++) {
            $user = (new User())
                ->setEmail($faker->email())
                ->setNickname($faker->userName())
                ->setDescription('')
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setValidationToken('ooo')
                ->setPlainPassword($pwd)
                ->setRoles(['ROLE_CLIENT']);
            $manager->persist($user);
        }

        $user = (new User())
                ->setEmail('admin@user.fr')
                ->setNickname('bgdu78')
                ->setFirstname('admin')
                ->setLastname('adminum')
                ->setValidationToken('ooo')
                ->setPlainPassword($pwd)
                ->setRoles(['ROLE_ADMIN'])
            ;
        $manager->persist($user);
        
        $manager->flush();
    }
}
