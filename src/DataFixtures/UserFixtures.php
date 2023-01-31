<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $pwd = 'Test1234';

        $names = array("Harry","Ross",
        "Bruce","Cook",
        "Carolyn","Morgan",
        "Albert","Walker",
        "Randy","Reed",
        "Larry","Barnes",
        "Lois","Wilson",
        "Jesse","Campbell",
        "Ernest","Rogers",
        "Theresa","Patterson",
        "Henry","Simmons",
        "Michelle","Perry",
        "Frank","Butler",
        "Shirley");

        foreach ($names as $name) {
            $user = (new User())
                ->setEmail($name . '@user.fr')
                ->setNickname('nick_' . $name)
                ->setDescription('je suis un agent')
                ->setFirstname($name)
                ->setLastname('l_' . $name)
                ->setValidationToken('ooo')
                ->setPlainPassword($pwd)
                ->setRoles(['ROLE_AGENT']);
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
