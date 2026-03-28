# DI-UGO-TEST-FULL-STACK

Application full stack : API **Symfony** (PHP) + interface **React** (TypeScript, Vite).

## Architecture (bonnes pratiques appliquées)

- **Backend** : séparation **présentation / métier** — DTO en `src/Dto/Api/` pour le contrat JSON, import CSV dans `src/Application/Import/`, commande console fine qui délègue au service. Réponses API via **Symfony Serializer** (avec `symfony/property-access`). Entités annotées **Validator** ; l’import valide chaque client et chaque commande avant persistance.
- **Frontend** : client HTTP centralisé avec **Axios** (instance unique, timeout, `Accept`), URL d’API configurable par variable d’environnement `VITE_API_BASE_URL` (voir `frontend/.env.example`).

## Prérequis

- PHP 8.3+ avec extensions `pdo_sqlite`, `mbstring`, `openssl`, `curl`, `zip` (recommandé pour Composer)
- [Composer](https://getcomposer.org/)
- Node.js 20+ et npm

## Backend (`backend/`)

### Installation

```bash
cd backend
composer install
```

### Base de données

SQLite est configurée dans `.env` :

```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

### Migrations

```bash
php bin/console doctrine:migrations:migrate
```

### Importer les CSV

Les fichiers `customers.csv` et `purchases.csv` sont attendus dans `backend/data/` (déjà fournis en exemple). Encodage CSV attendu : **UTF-8**, séparateur **virgule**, première ligne = en-têtes (noms de colonnes en **snake_case**).

**`customers.csv`**

| Colonne | Description |
|---------|-------------|
| `customer_number` | Identifiant métier unique (upsert) |
| `title` | `1` → `mme`, `2` → `m`, sinon valeur telle quelle |
| `last_name` | Nom |
| `first_name` | Prénom |
| `postal_code` | Code postal |
| `city` | Ville |
| `email` | Email (format valide requis) |

**`purchases.csv`**

| Colonne | Description |
|---------|-------------|
| `customer_number` | Référence vers un client existant (fichier clients ou déjà en base) |
| `purchase_identifier` | Identifiant d’achat |
| `product_id` | Référence produit |
| `quantity` | Entier > 0 |
| `price` | Nombre ≥ 0 |
| `currency` | Code devise (3 à 8 caractères, ex. `EUR`) |
| `date` | `YYYY-MM-DD`, ISO 8601 ou `Y-m-d H:i:s` |

```bash
php bin/console ugo:orders:import
```

La commande met à jour les clients par `customer_number` (upsert), supprime les commandes existantes puis réimporte les lignes de `purchases.csv`. Les lignes invalides (validation ou client inconnu) sont ignorées avec un avertissement en console.

### Lancer l’API (port 8000)

```bash
php -S localhost:8000 -t public
```

Endpoints :

- `GET http://localhost:8000/customers`
- `GET http://localhost:8000/customers/{id}/orders`

### Tests PHPUnit

```bash
cd backend
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/phpunit
```

## Frontend (`frontend/`)

### Installation

```bash
cd frontend
npm install
```

### Lancer le serveur de développement

```bash
npm run dev
```

Ou, équivalent proche de Create React App :

```bash
npm start
```

L’URL par défaut Vite est `http://localhost:5173`. Les en-têtes CORS du backend autorisent `localhost` et `127.0.0.1` sur n’importe quel port (dont `http://localhost:3000` si vous utilisez un autre outil) — voir `nelmio/cors-bundle` et `CORS_ALLOW_ORIGIN` dans `.env`.

Pour pointer vers une autre API (CI, Docker, etc.) : copiez `frontend/.env.example` vers `frontend/.env.local` et définissez `VITE_API_BASE_URL`.

### Build de production

```bash
npm run build
npm run preview
```

### Tests (Vitest + Testing Library)

```bash
npm test
```

Mode watch (développement) :

```bash
npm run test:watch
```

## Industrialisation (pistes)

- **Docker Compose** : un service `php-fpm` + nginx, un service Node pour le build des assets, volume pour SQLite ou bascule PostgreSQL.
- **CI/CD** : jobs parallèles `composer install` + `phpunit`, `npm ci` + `npm test` + `npm run build`.
- **API** : pagination sur les listes, versioning (`/v1/...`), authentification (JWT ou sessions), rate limiting.
- **Qualité** : PHPStan/Psalm, ESLint strict, déploiement avec migrations automatisées et sauvegardes DB.
