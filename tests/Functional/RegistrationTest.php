<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationTest extends WebTestCase
{
    use MailerAssertionsTrait;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Make sure this test starts from a clean state
        $existing = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'functional-test@example.com']);

        if ($existing) {
            $this->entityManager->remove($existing);
            $this->entityManager->flush();
        }
    }

    // Registering creates a client account, not yet verified, with a
    // hashed password, and sends a confirmation email
    public function testUserCanRegisterAndReceivesAConfirmationEmail(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('S\'inscrire', [
            'registration_form[name]' => 'Functional Test',
            'registration_form[email]' => 'functional-test@example.com',
            'registration_form[plainPassword]' => 'password123',
        ]);

        $this->assertResponseRedirects();
        $this->assertEmailCount(1);

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'functional-test@example.com']);

        $this->assertNotNull($user);
        $this->assertContains('ROLE_CLIENT', $user->getRoles());
        $this->assertFalse($user->isVerified());
        $this->assertNotSame('password123', $user->getPassword());
    }

    // Clicking the confirmation link in the email verifies the account
    public function testClickingTheConfirmationLinkVerifiesTheAccount(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('S\'inscrire', [
            'registration_form[name]' => 'Functional Test',
            'registration_form[email]' => 'functional-test@example.com',
            'registration_form[plainPassword]' => 'password123',
        ]);

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);

        preg_match('/href="([^"]+verify\/email[^"]+)"/', $email->getHtmlBody(), $matches);
        $confirmationUrl = html_entity_decode($matches[1]);

        // The user is automatically logged in after registration, so they
        // are authenticated when clicking the confirmation link
        $this->client->request('GET', $confirmationUrl);

        $this->entityManager->clear();
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'functional-test@example.com']);

        $this->assertTrue($user->isVerified());
    }
}
