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
        $types = ['Murder', 'Spy', 'Document theft', 'Target destruction', 'push'];

        foreach($types as $type) {
            $object = (new Type())
                ->setName($type)
            ;
            $manager->persist($object);
        }

        $manager->flush();
    }
}
