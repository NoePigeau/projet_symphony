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
        $users = $manager->getRepository(User::class)->findAll();
        $agents = array_filter($users, function($user) {
           return in_array('ROLE_AGENT', $user->getRoles());
        });
        $clients = array_filter($users, function($user) {
            return in_array('ROLE_CLIENT', $user->getRoles());
         });

         for ($i=0; $i<10; $i++) {
            $agent = $faker->randomElement($agents);
            $file = $faker->image('./public/upload/images/missions', 640, 480);
            $explodedFile = explode("/", $file);
            $endOfFile = end($explodedFile);
            $object = (new Mission())
                ->setName($faker->name)
                ->setDescription($faker->paragraph)
                ->setStatus('free')
                ->setClient($faker->randomElement($clients))
                ->setType($faker->randomElement($types))
                ->setReward(rand(10, 999))
                ->setImage($endOfFile)
            ;
            $manager->persist($object);
        }

        for ($i=0; $i<10; $i++) {
            $agent = $faker->randomElement($agents);
            $file = $faker->image('./public/upload/images/missions', 640, 480);
            $explodedFile = explode("/", $file);
            $endOfFile = end($explodedFile);
            $object = (new Mission())
                ->setName($faker->name)
                ->setDescription($faker->paragraph)
                ->setStatus('in_progress')
                ->setAgent($agent)
                ->setClient($faker->randomElement($clients))
                ->setType($faker->randomElement($agent->getType()))
                ->setReward(rand(10, 999))
                ->setImage($endOfFile)
            ;
            $manager->persist($object);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TypeFixtures::class,
            UserFixtures::class
        ];
    }
}
