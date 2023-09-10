<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface $encoder
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->encoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername("admin@admin.tn");
        $user->setEmail("admin@admin.tn");
        $user->setPassword($this->encoder->encodePassword($user,'123456'));
        $user->setRoles(['ROLE_ADMIN','ROLE_USER']);

        $user2 = new User();
        $user2->setUsername("utilisateur@test.tn");
        $user2->setEmail("utilisateur@test.tn");
        $user2->setPassword($this->encoder->encodePassword($user2,'utilisateur!'));
        $user2->setRoles(['ROLE_USER']);

        $manager->persist($user);
        $manager->persist($user2);
        $manager->flush();
    }
}
