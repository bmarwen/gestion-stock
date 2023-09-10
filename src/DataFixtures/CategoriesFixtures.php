<?php

namespace App\DataFixtures;

use App\Entity\Application;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoriesFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for($i = 0; $i < 5; $i++) {
            $category = new Category();
            $category->setName($faker->name);
            $category->setDescription($faker->text);
            $this->setReference('category_'. $i,$category);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
