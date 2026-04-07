<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccueilEtLivreTest extends WebTestCase
{
    // --- TESTS ACCUEIL ---

    public function testAccueilEstAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testAccueilContientTitre(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('h1')->count());
    }

    public function testPageLoginEstAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

   public function testLoginAvecMauvaisIdentifiants(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'faux@email.com',
            'password' => 'mauvais_mdp',
        ]));

        $this->assertResponseRedirects('/login');
    }

    public function testLoginAdminEtRedirection(): void
    {
        $client = static::createClient();
        $userRepo = static::getContainer()->get(UserRepository::class);
        $admin = $userRepo->findOneBy(['email' => 'admin@test.com']);
        $client->loginUser($admin);
        $client->request('GET', '/admin/dashboard');
        $this->assertResponseIsSuccessful();
    }

    // --- TESTS OUVRAGE ---

    public function testPageLivresAccessible(): void
    {
        $client = static::createClient();
        $userRepo = static::getContainer()->get(UserRepository::class);
        $admin = $userRepo->findOneBy(['email' => 'admin@test.com']);
        $client->loginUser($admin);
        $client->request('GET', '/librarian/livre');
        $this->assertResponseIsSuccessful();
    }

    public function testAdminPeutAccederAjoutLivre(): void
    {
        $client = static::createClient();
        $userRepo = static::getContainer()->get(UserRepository::class);
        $admin = $userRepo->findOneBy(['email' => 'admin@test.com']);
        $client->loginUser($admin);
        $client->request('GET', '/librarian/livre/new');
        $this->assertResponseIsSuccessful();
    }

    public function testUserNePeutPasAccederAjoutLivre(): void
    {
        $client = static::createClient();
        $userRepo = static::getContainer()->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => 'user@test.com']);
        $client->loginUser($user);
        $client->request('GET', '/librarian/livre/new');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testUserPeutVoirDetailLivre(): void
    {
        $client = static::createClient();
        $userRepo = static::getContainer()->get(UserRepository::class);
        $livreRepo = static::getContainer()->get(\App\Repository\LivreRepository::class);
        
        $user = $userRepo->findOneBy(['email' => 'user@test.com']);
        $livre = $livreRepo->findOneBy(['titre' => 'Le Petit Prince']);

        $client->loginUser($user);
        $client->request('GET', '/user/livre/' . $livre->getId());

        $this->assertResponseIsSuccessful();
    }
}