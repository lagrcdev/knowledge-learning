<?php

namespace App\DataFixtures;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend '
            . 'ante sem, id volutpat massa fermentum nec. Praesent volutpat scelerisque mauris, '
            . 'quis sollicitudin tellus sollicitudin.';

        // Themes, cursus and lessons, exactly as listed in the brief
        $data = [
            'Musique' => [
                ['name' => 'Cursus d\'initiation à la guitare', 'price' => 50, 'lessons' => [
                    ['title' => 'Découverte de l\'instrument', 'price' => 26],
                    ['title' => 'Les accords et les gammes', 'price' => 26],
                ]],
                ['name' => 'Cursus d\'initiation au piano', 'price' => 50, 'lessons' => [
                    ['title' => 'Découverte de l\'instrument', 'price' => 26],
                    ['title' => 'Les accords et les gammes', 'price' => 26],
                ]],
            ],
            'Informatique' => [
                ['name' => 'Cursus d\'initiation au développement web', 'price' => 60, 'lessons' => [
                    ['title' => 'Les langages Html et CSS', 'price' => 32],
                    ['title' => 'Dynamiser votre site avec Javascript', 'price' => 32],
                ]],
            ],
            'Jardinage' => [
                ['name' => 'Cursus d\'initiation au jardinage', 'price' => 30, 'lessons' => [
                    ['title' => 'Les outils du jardinier', 'price' => 16],
                    ['title' => 'Jardiner avec la lune', 'price' => 16],
                ]],
            ],
            'Cuisine' => [
                ['name' => 'Cursus d\'initiation à la cuisine', 'price' => 44, 'lessons' => [
                    ['title' => 'Les modes de cuisson', 'price' => 23],
                    ['title' => 'Les saveurs', 'price' => 23],
                ]],
                ['name' => 'Cursus d\'initiation à l\'art du dressage culinaire', 'price' => 48, 'lessons' => [
                    ['title' => 'Mettre en œuvre le style dans l\'assiette', 'price' => 26],
                    ['title' => 'Harmoniser un repas à quatre plats', 'price' => 26],
                ]],
            ],
        ];

        foreach ($data as $themeName => $cursusList) {
            $theme = new Theme();
            $theme->setName($themeName);
            $manager->persist($theme);

            foreach ($cursusList as $cursusData) {
                $cursus = new Cursus();
                $cursus->setName($cursusData['name']);
                $cursus->setPrice($cursusData['price']);
                $cursus->setTheme($theme);
                $manager->persist($cursus);

                foreach ($cursusData['lessons'] as $lessonData) {
                    $lesson = new Lesson();
                    $lesson->setTitle($lessonData['title']);
                    $lesson->setPrice($lessonData['price']);
                    $lesson->setContent($lorem);
                    $lesson->setVideoUrl('https://example.com/videos/placeholder.mp4');
                    $lesson->setCursus($cursus);
                    $manager->persist($lesson);
                }
            }
        }

        // Admin account, to test the back-office
        $admin = new User();
        $admin->setName('Admin');
        $admin->setEmail('admin@knowledge-learning.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin1234'));
        $manager->persist($admin);

        // Client account, to test purchases and lesson validation
        $client = new User();
        $client->setName('Client');
        $client->setEmail('client@example.com');
        $client->setIsVerified(true);
        $client->setPassword($this->passwordHasher->hashPassword($client, 'client1234'));
        $manager->persist($client);

        $manager->flush();
    }
}
