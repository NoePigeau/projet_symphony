<?php

namespace App\DataFixtures;

use App\Entity\Mission;
use App\Entity\Type;
use App\Entity\User;
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
        $agents = $manager->getRepository(User::class)->findAll();
        $clients = $manager->getRepository(User::class)->findAll();

        for ($i=0; $i<10; $i++) {
            $object = (new Mission())
                ->setName($faker->name)
                ->setDescription($faker->paragraph)
                ->setStatus('free')
                ->setAgent($faker->randomElement($agents))
                ->setClient($faker->randomElement($clients))
                ->setType($faker->randomElement($types))
                ->setSlug(strtolower($faker->name))
                ->setReward(12)
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
