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
        $faker = Factory::create('fr_FR');

        for ($i=0; $i<10; $i++) {
            $object = (new Type())
                ->setName($faker->randomElement(['Meurte', 'Espionnage', 'Vole de document', 'Destruction d\' objectif', 'Coup d\' état']))
            ;
            $manager->persist($object);
        }

        $manager->flush();
    }
}
