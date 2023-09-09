<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        
        $user = new User();
        $user->setEmail('rawi@gmail.com');
        $user->setPassword('$2y$13$PZC1V8QyHZpOowopjdAcmesxBYUhYxpbRMVhMieW73PS/1LOxdcU2');
        $manager->persist($user);

        $user2 = new User();
        $user2->setEmail('nubiya@gmail.com');
        $user2->setPassword('$2y$13$PZC1V8QyHZpOowopjdAcmesxBYUhYxpbRMVhMieW73PS/1LOxdcU2');
        $manager->persist($user2);

        $token = new ApiToken();
        $token->setToken('cbd0837f12ff31223b4adf34dfbad7f0365d7b54452c583ff727ed1bec6e80a1b174c340027f282fedba2f47a2532118b1f6a218a86f2fc42efd0a7f');
        $token->setUser($user);
        $manager->persist($token);

        $manager->flush();
    }
}
