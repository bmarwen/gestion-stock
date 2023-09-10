<?php

namespace App\DataFixtures;

use App\Entity\Application;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductsFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for($i = 0; $i < 40; $i++) {
            $product = new Product();
            $product->setName($faker->name);
            $product->setCode($faker->randomNumber(8));
            $product->setGain($faker->numberBetween(0, 40));
            $product->setTva($faker->numberBetween(0, 20));
            $product->setMark($faker->randomElement(['ACM', 'Addax', 'Alodont', 'Alphanova']));
            $product->setPurchacePriceUnHt($faker->numberBetween(1000, 30000));
            $product->setHowMany($faker->numberBetween(0,10));
            $product->setCategory($this->getReference('category_' . $faker->numberBetween(0,4)));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
