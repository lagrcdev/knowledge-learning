<?php

namespace App\Tests\Repository;

use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ThemeRepositoryTest extends KernelTestCase
{
    private ThemeRepository $themeRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->themeRepository = self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(\App\Entity\Theme::class);
    }

    // Functional test: the themes loaded by the fixtures can be found
    public function testFindAllReturnsTheFourThemes(): void
    {
        $themes = $this->themeRepository->findAll();
        $names = array_map(fn ($theme) => $theme->getName(), $themes);

        $this->assertContains('Musique', $names);
        $this->assertContains('Informatique', $names);
        $this->assertContains('Jardinage', $names);
        $this->assertContains('Cuisine', $names);
    }

    // Defensive/robustness test: looking up a theme that does not exist
    // must return null, not throw an exception
    public function testFindNonExistentThemeReturnsNull(): void
    {
        $theme = $this->themeRepository->find(999999);

        $this->assertNull($theme);
    }
}
