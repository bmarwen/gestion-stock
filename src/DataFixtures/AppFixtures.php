<?php

namespace App\DataFixtures;

use App\Entity\Application;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private const DEFAULT_PARAMS_INFORMATIONS = [
        'free_shipping_min_price',
        'shipping_price'
    ];

    private const DEFAULT_PARAMS_IMAGES = [
        'image_home_bottom_left_1',
        'image_home_top_left_1',
        'image_home_top_left_2',
        'image_home_bottom_center',
        'image_home_bottom_right_1',
        'image_home_top_right_slider1',
        'image_home_top_right_slider2',
        'image_home_top_right_slider3'
    ];

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        foreach (self::DEFAULT_PARAMS_INFORMATIONS as $INFORMATION) {
            $applicationParam = new Application();
            $applicationParam->setName($INFORMATION);
            $applicationParam->setValue($faker->randomDigitNotNull);
            $applicationParam->setType("information");
            $applicationParam->setRedirectTo("/");
            $manager->persist($applicationParam);
        }

        foreach (self::DEFAULT_PARAMS_IMAGES as $INFORMATION) {
            $applicationParam = new Application();
            $applicationParam->setName($INFORMATION);
            $applicationParam->setValue(0);
            $applicationParam->setType("image");
            $applicationParam->setRedirectTo("/");
            $applicationParam->setFilename('default.png');
            $manager->persist($applicationParam);
        }

        $manager->flush();
    }
}
