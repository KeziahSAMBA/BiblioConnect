<?php

namespace App\Tests\Unit;

use App\Entity\User;
use App\Entity\Livre;
use App\Entity\Categorie;
use PHPUnit\Framework\TestCase;

class LoginAndLivreTest extends TestCase
{
    // --- TEST LOGIN ---

    public function testUserHasCorrectEmail(): void
    {
        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setRoles(['ROLE_ADMIN']);

        $this->assertSame('admin@test.com', $user->getEmail());
    }

    public function testUserHasAdminRole(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testUserDefaultRoleIsUser(): void
    {
        $user = new User();
        // ROLE_USER est toujours inclus par Symfony par défaut
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    // --- TEST AJOUT OUVRAGE ---

    public function testLivreCreation(): void
    {
        $categorie = new Categorie();
        $categorie->setNom('Science-fiction');

        $livre = new Livre();
        $livre->setTitre('Dune');
        $livre->setAuteur('Frank Herbert');
        $livre->setLangue('Français');
        $livre->setStock(10);
        $livre->setCategorie($categorie);

        $this->assertSame('Dune', $livre->getTitre());
        $this->assertSame('Frank Herbert', $livre->getAuteur());
        $this->assertSame('Français', $livre->getLangue());
        $this->assertSame(10, $livre->getStock());
        $this->assertSame('Science-fiction', $livre->getCategorie()->getNom());
    }

    public function testLivreStockIsPositive(): void
    {
        $livre = new Livre();
        $livre->setStock(3);

        $this->assertGreaterThan(0, $livre->getStock());
    }

    public function testLivreSansImageEstValide(): void
    {
        $livre = new Livre();
        $livre->setTitre('Test');
        $livre->setAuteur('Auteur');
        $livre->setLangue('Français');
        $livre->setStock(1);

        $this->assertNull($livre->getImage());
    }
}