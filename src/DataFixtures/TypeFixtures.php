<?php

namespace App\DataFixtures;

use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');

        for ($i=0; $i<10; $i++) {
            $object = (new Type())
                ->setName($faker->randomElement(['Murder', 'Spy', 'Document theft', 'Target destruction', 'Coup d\'etat']))
            ;
            $manager->persist($object);
        }

        $manager->flush();
    }
}
