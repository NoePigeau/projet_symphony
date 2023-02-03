<?php

namespace App\DataFixtures;

use App\Entity\Equipment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EquipmentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $equipment = (new Equipment)
            ->setName('Revolver')
            ->setDescription('Un revolver pour bang')
            ->setImage('')
            ->setStock(54);
           
        $manager->persist($equipment);

        $equipment = (new Equipment)
            ->setName('Belle voiture')
            ->setDescription('VROOOOOMMMM')
            ->setImage('')
            ->setStock(15);
           
        $manager->persist($equipment);

        $equipment = (new Equipment)
        ->setName('Couteau')
        ->setDescription('Ça c\'est des vrai couteaux')
        ->setImage('')
        ->setStock(103);
       
        $manager->persist($equipment);

        $equipment = (new Equipment)
        ->setName('Tazer')
        ->setDescription('zzzzztt')
        ->setImage('')
        ->setStock(67);
       
        $manager->persist($equipment);

        $manager->flush();
    }
}