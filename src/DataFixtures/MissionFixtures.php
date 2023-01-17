<?php

namespace App\DataFixtures;

use App\Entity\Mission;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MissionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $types = $manager->getRepository(Type::class)->findAll();

        for ($i=0; $i<10; $i++) {
            $object = (new Mission())
                ->setName($faker->name)
                ->setDescription($faker->paragraph)
                ->setStartDate($faker->dateTimeBetween('-1 years'))
                ->setType($faker->randomElement($types))
            ;
            $manager->persist($object);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TypeFixtures::class
        ];
    }
}
