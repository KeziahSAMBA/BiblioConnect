# 📚 BiblioConnect

Plateforme de gestion de bibliothèque moderne développée avec **Symfony 7**, offrant un espace usager, une interface bibliothécaire et un panneau d'administration complet.

---

## 🚀 Technologies

- **PHP 8.5** / **Symfony 7**
- **Doctrine ORM** + Migrations
- **Twig** (templates)
- **MySQL** (MAMP, port 3307)
- **PHPUnit 13** (tests unitaires & fonctionnels)
- **Webpack Encore**

---

## ⚙️ Installation

### 1. Cloner le projet

```bash
git clone https://github.com/TON_USERNAME/BiblioConnect.git
cd BiblioConnect
```

### 2. Installer les dépendances

```bash
composer install
npm install
```

### 3. Configurer l'environnement

Copier le fichier `.env` et adapter les variables :

```bash
cp .env .env.local
```

Modifier la ligne `DATABASE_URL` dans `.env.local` :

```env
DATABASE_URL="mysql://root:root@127.0.0.1:3307/bibliconnect?serverVersion=8.0&charset=utf8mb4"
```

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Charger les données de test

```bash
php bin/console doctrine:fixtures:load
```

### 6. Lancer le serveur

```bash
symfony serve
```

L'application est accessible sur `http://127.0.0.1:8000`

---

## 🔐 Comptes de test

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Administrateur | admin@test.com | admin |
| Bibliothécaire | librarian@test.com | librarian |

---

## 👥 Rôles & fonctionnalités

### ROLE_USER — Usager
- Inscription / connexion avec double vérification du mot de passe
- Recherche d'ouvrages (titre, auteur, catégorie)
- Réservation de livres selon un planning
- Mise en favoris des livres
- Consultation des réservations et favoris dans le profil
- Commentaires et notation (⭐ 1 à 5 étoiles)

### ROLE_LIBRARIAN — Bibliothécaire
- Gestion du catalogue (titres, auteurs, catégories, langues, images)
- Ajout de fiches détaillées pour chaque ouvrage
- Consultation de l'historique des réservations

### ROLE_ADMIN — Administrateur
- Accès total à toutes les fonctionnalités
- Gestion des rôles et profils utilisateurs
- Suivi des stocks et réservations en attente
- Modération des commentaires
- Consultation de l'historique complet des réservations

---

## 🔄 Redirection automatique par rôle

Après connexion, chaque utilisateur est automatiquement redirigé vers son espace :

- **Admin** → `/admin/dashboard`
- **Bibliothécaire** → `/librarian/dashboard`
- **Utilisateur** → `/user/profil`

---

## 🧪 Tests

### Configuration de l'environnement de test

```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --no-interaction --env=test
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

### Lancer les tests

```bash
# Tous les tests
php bin/phpunit --testdox

# Tests unitaires uniquement
php bin/phpunit tests/Unit/ --testdox

# Tests fonctionnels uniquement
php bin/phpunit tests/Functional/ --testdox
```

### Résultats attendus

```
Accueil Et Livre (App\Tests\Functional\AccueilEtLivre)
 ✔ Accueil est accessible
 ✔ Accueil contient titre
 ✔ Page login est accessible
 ✔ Login avec mauvais identifiants
 ✔ Login admin et redirection
 ✔ Page livres accessible
 ✔ Admin peut acceder ajout livre
 ✔ User ne peut pas acceder ajout livre
 ✔ User peut voir detail livre

Login And Livre (App\Tests\Unit\LoginAndLivre)
 ✔ User has correct email
 ✔ User has admin role
 ✔ User default role is user
 ✔ Livre creation
 ✔ Livre stock is positive
 ✔ Livre sans image est valide

Registration Controller (App\Tests\RegistrationController)
 ✔ Register

OK (16 tests, 22 assertions)
```

---

## 📁 Structure du projet

```
src/
├── Controller/
│   ├── Admin/          # Contrôleurs admin
│   ├── Librarian/      # Contrôleurs bibliothécaire
│   ├── User/           # Contrôleurs usager
│   └── SecurityController.php
├── Entity/
│   ├── User.php
│   ├── Livre.php
│   ├── Categorie.php
│   ├── Reservation.php
│   ├── Commentaire.php
│   └── Favori.php
├── Form/
├── Repository/
├── Security/
│   └── AppAuthenticator.php
└── DataFixtures/
    ├── AppFixtures.php
    └── TestFixtures.php
templates/
tests/
├── Functional/
│   └── AccueilEtLivreTest.php
├── Unit/
│   └── LoginAndLivreTest.php
└── RegistrationControllerTest.php
```

---

## 📝 Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Voir toutes les routes
php bin/console debug:router

# Générer une migration
php bin/console doctrine:migrations:diff

# Appliquer les migrations
php bin/console doctrine:migrations:migrate
```

---

## 👩‍💻 Auteur

Projet réalisé dans le cadre de la formation **achiDev 2026** — Symfony 35h.
