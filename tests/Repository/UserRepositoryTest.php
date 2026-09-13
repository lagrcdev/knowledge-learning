<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->userRepository = self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(User::class);
    }

    // Functional test: the admin created by the fixtures can be found by email
    public function testFindAdminByEmail(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'admin@knowledge-learning.com']);

        $this->assertNotNull($user);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    // Security test: passwords are never stored in plain text
    public function testPasswordIsNeverStoredInPlainText(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'admin@knowledge-learning.com']);

        $this->assertNotSame('admin1234', $user->getPassword());
    }

    // Security test: looking up an email that does not exist must return
    // null, not leak any information about existing accounts
    public function testFindByUnknownEmailReturnsNull(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'does-not-exist@example.com']);

        $this->assertNull($user);
    }
}
