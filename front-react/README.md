# ECOAL React Front V1

Front minimaliste en React pour tester l'API Laravel ECOAL (auth, collections, items, catégories, critères, scores).

## Prérequis

- PHP + Composer
- Node.js + npm

## Lancer le backend

Dans le dossier racine Laravel (`ECOAL_API`) :

```bash
php artisan serve
```

Par défaut : `http://127.0.0.1:8000`.

## Lancer le front

Dans le dossier `front-react` :

```bash
npm install
npm run dev
```

Par défaut : `http://localhost:5174`.

## Configuration API

Le front lit la base URL via la variable d'environnement :

- `VITE_API_BASE_URL` dans `front-react/.env`

Par défaut :

```bash
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

## Fonctionnalités

- Authentification (register, login, logout, /user) via Sanctum (Bearer token).
- Pages :
  - Dashboard (protégé)
  - Login
  - Register
  - Collections
  - Items
  - Categories
  - Criteria
  - Scores
- Client API centralisé (`src/api/client.js`) avec log des requêtes.
- Contexte d'auth (`src/context/AuthContext.jsx`) avec persistance du token dans `localStorage`.
- Log des requêtes et vue détaillée de la dernière réponse (`LogViewer`).

