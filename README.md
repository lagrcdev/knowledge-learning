# Knowledge Learning

Plateforme e-learning pour la société fictive Knowledge. Les utilisateurs peuvent acheter des
cursus ou des leçons, les valider, et obtenir une certification quand un cursus est terminé.

Projet réalisé avec Symfony 8.1, Doctrine ORM et MySQL.

## Prérequis

- PHP 8.4+
- Composer
- MySQL/MariaDB
- Un compte Stripe (mode test)

## Installation

```bash
composer install
cp .env .env.local
```

Dans `.env.local`, renseigner :

```
APP_SECRET=une_chaine_aleatoire
DATABASE_URL="mysql://root:@127.0.0.1:3306/knowledge_learning?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_PUBLIC_KEY=pk_test_xxx
```

Puis créer la base et charger les données de démo :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

Deux comptes de test sont créés :
- Admin : `admin@knowledge-learning.com` / `admin1234`
- Client : `client@example.com` / `client1234`

## Lancement

```bash
symfony server:start
```

Site accessible sur http://localhost:8000.

Paiement en mode test Stripe : carte `4242 4242 4242 4242`, date future, CVC quelconque.

## Tests

```bash
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:migrations:migrate
php bin/console --env=test doctrine:fixtures:load
php bin/phpunit
```
