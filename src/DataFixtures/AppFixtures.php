<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Product;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($i=0; $i < 10 ; $i++) { 
            $product = new Product();
            $product->setName($faker->name);
            $product->setPrice($faker->randomFloat());
            $product->setQuantity($faker->randomDigit);
            $product->setCreatedAt($faker->dateTime());
            $manager->persist($product);
        }
        $manager->flush();
    }
}
