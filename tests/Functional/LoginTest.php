<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    // A verified user can log in with the right credentials
    public function testUserCanLoginWithCorrectCredentials(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'client1234',
        ]);

        $this->assertResponseRedirects('/');

        // Once logged in, a page reserved to clients must be reachable
        $client->request('GET', '/certifications');
        $this->assertResponseIsSuccessful();
    }

    // A wrong password must not authenticate the user
    public function testUserCannotLoginWithWrongPassword(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'wrong-password',
        ]);

        $this->assertResponseRedirects('/login');

        // Still not authenticated: the certifications page redirects to login
        $client->request('GET', '/certifications');
        $this->assertResponseRedirects();
    }
}
