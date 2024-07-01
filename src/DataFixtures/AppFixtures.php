<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Faker\Generator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private Generator $faker;

    private UserPasswordHasherInterface $userPasswordHasher; 
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher){
        $this->faker = Factory::create("fr_FR");
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $username = $this->faker->userName();
        $adminUser = new User();
        $adminUser->setUsername("1234azerty")
        ->setRoles(["ROLE_ADMIN"])->setPassword($this->userPasswordHasher->hashPassword($adminUser, "1234azerty") )->setEmail("admin@gmail.com");
        $manager->persist($adminUser);
        for($i = 0; $i < 10; $i++){
                $password = $this->faker->password(6,10);
                $username = $this->faker->userName();
        $userUser = new User();
        $userUser->setUsername($username. "/". $password)
        ->setRoles(["ROLE_USER"])->setPassword($this->userPasswordHasher->hashPassword($userUser,$password) )->setEmail($username. "@gmail.com");
        $manager->persist($userUser);
    }
        $manager->flush();
    }
}