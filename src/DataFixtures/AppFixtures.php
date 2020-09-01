<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $api = new ApiToken();

        $api->setToken(md5(uniqid(rand(), true)));

        $manager->persist($api);

        $manager->flush();
    }
}
