# AI Writing API

API SaaS de rédaction assistée par IA avec Laravel et OpenAI.

## Description

Cette API REST permet aux utilisateurs d'utiliser l'intelligence artificielle pour :
- **Générer du texte** à partir d'un prompt
- **Résumer** des textes longs
- **Réécrire** du contenu pour l'améliorer
- **Générer des questions** à partir d'un texte

## Architecture du projet

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php      # Authentification (register, login, logout)
│   │   └── AIController.php        # Endpoints IA
│   └── Requests/
│       ├── Auth/
│       │   ├── LoginRequest.php
│       │   └── RegisterRequest.php
│       └── AI/
│           ├── GenerateTextRequest.php
│           ├── SummarizeRequest.php
│           ├── RewriteRequest.php
│           └── QuestionsRequest.php
├── Jobs/
│   └── ProcessAIRequestJob.php     # Traitement asynchrone des requêtes IA
├── Models/
│   ├── User.php                    # Utilisateur avec quota
│   └── AIRequest.php               # Historique des requêtes IA
└── Services/
    └── OpenAIService.php           # Service d'appel à l'API OpenAI
```

## Les différentes installations indispensables

```bash
# Cloner le projet
git clone <repository>
cd ai-writing-api

# Installer les dépendances
composer install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Configurer la base de données dans .env

# Exécuter les migrations
php artisan migrate

# Lancer le serveur
php artisan serve
```

## Les Configurations à faire dans le fichier .env

### Variables d'environnement

```env
# OpenAI
OPENAI_API_KEY=your-openai-api-key-here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=2000
OPENAI_TEMPERATURE=0.7
```

### Queue Worker (pour le traitement asynchrone)

```bash
php artisan queue:work
```

## LEs Endpoints pour l'API

### Authentification (Pour la connexion, l'inscription, la déconnexion et pour voir son profil)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/auth/register` | Inscription |
| POST | `/api/auth/login` | Connexion |
| POST | `/api/auth/logout` | Déconnexion |
| GET | `/api/auth/me` | Profil utilisateur |

### IA (L'authentification est obligatoire pour accéder à ces routes, il faut donc s'inscrire, se connecter et utiliser la clé de la connexion afin de pouvoir accéder à chacune de ces routes)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/ai/generate-text` | Génération de texte |
| POST | `/api/ai/summarize` | Résumé de texte |
| POST | `/api/ai/rewrite` | Réécriture de texte |
| POST | `/api/ai/questions` | Génération de questions |
| GET | `/api/ai/history` | Historique des requêtes |
| GET | `/api/ai/request/{id}` | Détail d'une requête |

## Exemples d'utilisation

### Pour l'inscription

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Pour la connexion

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Pour la génération de texte

```bash
curl -X POST http://localhost:8000/api/ai/generate-text \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "prompt": "Écris un article sur les avantages du télétravail"
  }'
```

### Pour le résumé de texte

```bash
curl -X POST http://localhost:8000/api/ai/summarize \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "text": "Votre texte long à résumer ici..."
  }'
```

## Authentification

L'API utilise **Laravel Sanctum** pour l'authentification via tokens.

1. S'inscrire ou se connecter pour obtenir un token
2. Inclure le token dans le header `Authorization: Bearer YOUR_TOKEN`

## Système de quota

Chaque utilisateur dispose d'un quota de requêtes IA :
- **Plan free** : 10 requêtes
- Le quota est décrémenté à chaque requête réussie

## Tests

```bash
php artisan test
```

## Technologies

- **Laravel 12** - Framework PHP
- **Laravel Sanctum** - Authentification API
- **OpenAI API** - Intelligence artificielle
- **SQLite/MySQL** - Base de données
- **Queue Jobs** - Traitement asynchrone

## License

MIT License
