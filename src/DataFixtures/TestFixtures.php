<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Livre;
use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Test');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'password123'));
        $admin->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($admin);

        // User simple
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setNom('User');
        $user->setPrenom('Test');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->hasher->hashPassword($user, 'password123'));
        $user->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($user);

        // Catégorie
        $categorie = new Categorie();
        $categorie->setNom('Roman');
        $manager->persist($categorie);

        // Livre
        $livre = new Livre();
        $livre->setTitre('Le Petit Prince');
        $livre->setAuteur('Antoine de Saint-Exupéry');
        $livre->setLangue('Français');
        $livre->setStock(5);
        $livre->setCategorie($categorie);
        $manager->persist($livre);

        $manager->flush();

        $this->addReference('admin', $admin);
        $this->addReference('user', $user);
        $this->addReference('categorie', $categorie);
    }
}