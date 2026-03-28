# Test full stack — Symfony + React

Stack : Symfony 7 (SQLite), React 19 + TypeScript, Vite. En développement, Vite proxifie `/api` vers `http://127.0.0.1:8000` (même origine, moins de latence CORS). Pour forcer une URL absolue, définir `VITE_API_BASE_URL` (voir `frontend/.env.example`). En build de prod sans variable, l’API par défaut est `http://localhost:8000`.

## Prérequis

PHP 8.2+ (pdo_sqlite, extensions habituelles pour Composer), Composer, Node 20+ et npm.

## Backend

```bash
cd backend
composer install
```

Base SQLite, URL dans `.env` :

```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

Migrations :

```bash
php bin/console doctrine:migrations:migrate
```

Import des données : déposer `customers.csv` et `purchases.csv` dans `backend/data/` (des exemples sont déjà là). UTF-8, virgule, première ligne = en-têtes. Civilité dans le CSV : `1` → `mme`, `2` → `m`. Puis :

```bash
php bin/console ugo:orders:import
```

À chaque import, les commandes en base sont remplacées par le contenu du CSV ; les clients sont fusionnés par `customer_number`. Une ligne invalide est ignorée avec un message dans le terminal.

Lancer l’API :

```bash
php -S localhost:8000 -t public
```

- `GET /customers`
- `GET /customers/{id}/orders`

CORS est ouvert pour `localhost` et `127.0.0.1` (config Nelmio + `.env`).

Tests :

```bash
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/phpunit
```

## Frontend

```bash
cd frontend
npm install
npm start
```

(Vite écoute en général sur http://localhost:5173 — le `npm start` du sujet est prévu.) Le backend doit tourner sur le port 8000 pour que le proxy `/api` fonctionne.

Build :

```bash
npm run build
```

Tests :

```bash
npm test
```

(`npm run test:watch` pour laisser Vitest tourner pendant qu’on code.)

## CSV — colonnes attendues

**customers.csv** : `customer_number`, `title`, `last_name`, `first_name`, `postal_code`, `city`, `email`.

**purchases.csv** : `customer_number`, `purchase_identifier`, `product_id`, `quantity`, `price`, `currency`, `date` (par ex. `2024-01-15` ou format ISO).

## Après ce test

Si je devais aller plus loin : mettre l’API et la base dans Docker, brancher une CI qui lance `composer install` / `phpunit` et `npm ci` / `npm test` / `npm run build`, paginer les listes côté API et ajouter au minimum une auth basique avant toute mise en ligne publique. PostgreSQL à la place de SQLite dès qu’on sort du local.
