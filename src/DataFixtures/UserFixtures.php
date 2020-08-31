<?php

namespace App\DataFixtures;

use App\Entity\Restaurants;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'adminas'));
        $manager->persist($user);
        $manager->flush();

        $restaurant = new Restaurants();
        $restaurant->setTitle('testinis pavadinimas');
        $restaurant->setPhoto('there will be photo');
        $restaurant->setMaxTable(5);
        $restaurant->setStatus(1);
        $manager->persist($restaurant);
        $manager->flush();

        $restaurant = new Restaurants();
        $restaurant->setTitle('antras pavadinimas');
        $restaurant->setPhoto('there will be photo');
        $restaurant->setMaxTable(4);
        $restaurant->setStatus(1);
        $manager->persist($restaurant);
        $manager->flush();
    }

}
