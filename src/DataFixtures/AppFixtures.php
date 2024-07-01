<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Link;
use App\Entity\Song;
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
        // Create an admin user
        $username = "userAdmin";
        $adminUser = new User();
        $adminUser->setUsername($username)
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword($this->userPasswordHasher->hashPassword($adminUser, $username) )
            ->setEmail("admin@gmail.com");

        $manager->persist($adminUser);

        // Create 10 random users
        for($i = 0; $i < 10; $i++){
            $password = $this->faker->password(6,10);
            $username = $this->faker->userName();

            $user = new User();
            $user->setUsername($username. "/". $password)
                ->setRoles(["ROLE_USER"])
                ->setPassword($this->userPasswordHasher->hashPassword($user,$password) )
                ->setEmail($username. "@gmail.com");

            $manager->persist($user);
        }

        $manager->flush();

        // Create 20 songs
        $songs = [];
        for($i = 0; $i < 20; $i++){
            $song = new Song();
            $song->setTitle($this->faker->word())
                ->setArtist($this->faker->name())
                ->setAlbum($this->faker->word())
                ->setStatus("on");

            $songs[] = $song;       
            $manager->persist($song);
        }

        $manager->flush();

        // Create 10 links
        for($i = 0; $i < 10; $i++){
            $link = new Link();
            $link->setUrl($this->faker->url());

            // Add a random number of songs to the link
            $randomSongs = $this->faker->randomElements($songs, $this->faker->numberBetween(1, 3));
            foreach($randomSongs as $song){
                $link->addSong($song);
            }

            $manager->persist($link);
        }
        
        $manager->flush();
    }
}