# BlogFlow API

API REST pour une plateforme de blog, construite avec **Laravel 11** et sécurisée avec **Laravel Sanctum**.

- **URL de production** : `https://blog-api-service-fbnq.onrender.com`
- **Préfixe de toutes les routes** : `/api`

---

## Sommaire

- [Technologies](#technologies)
- [Authentification](#authentification)
- [Modèles de données](#modèles-de-données)
- [Endpoints](#endpoints)
  - [Authentification](#-authentification)
  - [Articles](#-articles)
  - [Commentaires](#-commentaires)
  - [Tableau de bord](#-tableau-de-bord)
- [Codes d'erreur](#codes-derreur)
- [Installation locale](#installation-locale)
- [Comptes de test](#comptes-de-test)

---

## Technologies

| Outil | Version |
|---|---|
| PHP | 8.4 |
| Laravel | 11 |
| Authentification | Laravel Sanctum (tokens Bearer) |
| Base de données (prod) | PostgreSQL (Render) |
| Base de données (local) | MySQL / SQLite |
| Stockage images | Laravel Storage (disk `public`) |

---

## Authentification

L'API utilise des **tokens Bearer** via Laravel Sanctum.

Les routes publiques sont accessibles sans token. Les routes protégées nécessitent d'envoyer le token dans l'en-tête HTTP :

```
Authorization: Bearer {votre_token}
```

Le token est retourné dans la réponse des endpoints `/register` et `/login`.

---

## Modèles de données

### User
| Champ | Type | Description |
|---|---|---|
| `id` | integer | Identifiant unique |
| `name` | string | Nom complet |
| `email` | string | Adresse email (unique) |
| `created_at` | datetime | Date de création |

### Category
| Champ | Type | Description |
|---|---|---|
| `id` | integer | Identifiant unique |
| `name` | string | Nom de la catégorie |

### Article
| Champ | Type | Description |
|---|---|---|
| `id` | integer | Identifiant unique |
| `title` | string | Titre (max 191 caractères) |
| `short_description` | string | Description courte (max 255 caractères) |
| `content` | text | Contenu complet |
| `image` | string\|null | URL complète de l'image de couverture |
| `user_id` | integer | ID de l'auteur |
| `category_id` | integer | ID de la catégorie |
| `created_at` | datetime | Date de publication |
| `updated_at` | datetime | Date de mise à jour |

### Comment
| Champ | Type | Description |
|---|---|---|
| `id` | integer | Identifiant unique |
| `visitor_name` | string | Nom du visiteur (max 191 caractères) |
| `visitor_email` | string | Email du visiteur (max 191 caractères) |
| `message` | text | Contenu du commentaire (max 1000 caractères) |
| `article_id` | integer | ID de l'article associé |
| `created_at` | datetime | Date de publication |

---

## Endpoints

### 🔓 Authentification

---

#### `POST /api/register`

Créer un nouveau compte utilisateur.

**Accès** : Public

**Corps de la requête** (`application/json`)

```json
{
  "name": "Sophie Marchand",
  "email": "sophie@exemple.fr",
  "password": "motdepasse123",
  "password_confirmation": "motdepasse123"
}
```

**Règles de validation**

| Champ | Règle |
|---|---|
| `name` | Requis, chaîne, max 255 caractères |
| `email` | Requis, format email valide, unique |
| `password` | Requis, minimum 8 caractères, confirmé |
| `password_confirmation` | Doit correspondre à `password` |

**Réponse — 201 Created**

```json
{
  "message": "Utilisateur créé avec succès !",
  "user": {
    "id": 6,
    "name": "Sophie Marchand",
    "email": "sophie@exemple.fr",
    "created_at": "2025-01-15T10:30:00.000000Z"
  },
  "access_token": "1|abc123xyz...",
  "token_type": "Bearer"
}
```

**Réponse — 422 Unprocessable Entity** *(validation échouée)*

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

#### `POST /api/login`

Connecter un utilisateur existant.

**Accès** : Public

**Corps de la requête** (`application/json`)

```json
{
  "email": "admin@blogflow.fr",
  "password": "Admin@2024!"
}
```

**Règles de validation**

| Champ | Règle |
|---|---|
| `email` | Requis, format email valide |
| `password` | Requis |

**Réponse — 200 OK**

```json
{
  "message": "Connexion réussie !",
  "user": {
    "id": 1,
    "name": "Sophie Marchand",
    "email": "admin@blogflow.fr",
    "created_at": "2025-01-15T10:00:00.000000Z"
  },
  "access_token": "2|def456uvw...",
  "token_type": "Bearer"
}
```

**Réponse — 401 Unauthorized** *(identifiants incorrects)*

```json
{
  "message": "Identifiants incorrects."
}
```

---

#### `POST /api/logout`

Révoquer le token de l'utilisateur connecté.

**Accès** : 🔒 Authentifié

**En-têtes requis**
```
Authorization: Bearer {token}
```

**Corps de la requête** : Aucun

**Réponse — 200 OK**

```json
{
  "message": "Déconnexion réussie !"
}
```

---

### 📄 Articles

---

#### `GET /api/articles`

Récupérer la liste paginée des articles (10 par page).

**Accès** : Public

**Paramètres de requête (query params)**

| Paramètre | Type | Requis | Description |
|---|---|---|---|
| `page` | integer | Non | Numéro de page (défaut : 1) |
| `search` | string | Non | Recherche dans le titre et le contenu |
| `category_id` | integer | Non | Filtrer par ID de catégorie |

**Exemples**

```
GET /api/articles
GET /api/articles?page=2
GET /api/articles?search=react
GET /api/articles?category_id=1
GET /api/articles?search=laravel&page=1
```

**Réponse — 200 OK**

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "title": "Les fondamentaux de React 19",
      "short_description": "React 19 introduit des changements profonds...",
      "content": "React 19 est sans doute la mise à jour...",
      "image": "https://blog-api-service-fbnq.onrender.com/storage/articles/photo.jpg",
      "user_id": 1,
      "category_id": 1,
      "created_at": "2025-01-15T10:00:00.000000Z",
      "updated_at": "2025-01-15T10:00:00.000000Z",
      "category": {
        "id": 1,
        "name": "Développement Web"
      },
      "user": {
        "id": 1,
        "name": "Sophie Marchand"
      }
    }
  ],
  "first_page_url": "https://.../api/articles?page=1",
  "last_page": 2,
  "last_page_url": "https://.../api/articles?page=2",
  "next_page_url": "https://.../api/articles?page=2",
  "prev_page_url": null,
  "per_page": 10,
  "total": 12
}
```

---

#### `GET /api/articles/{id}`

Récupérer le détail complet d'un article avec sa catégorie, son auteur et ses commentaires.

**Accès** : Public

**Paramètre d'URL**

| Paramètre | Type | Description |
|---|---|---|
| `id` | integer | ID de l'article |

**Exemple**

```
GET /api/articles/1
```

**Réponse — 200 OK**

```json
{
  "id": 1,
  "title": "Les fondamentaux de React 19",
  "short_description": "React 19 introduit des changements profonds...",
  "content": "React 19 est sans doute la mise à jour la plus significative...",
  "image": "https://blog-api-service-fbnq.onrender.com/storage/articles/photo.jpg",
  "user_id": 1,
  "category_id": 1,
  "created_at": "2025-01-15T10:00:00.000000Z",
  "updated_at": "2025-01-15T10:00:00.000000Z",
  "category": {
    "id": 1,
    "name": "Développement Web"
  },
  "user": {
    "id": 1,
    "name": "Sophie Marchand"
  },
  "comments": [
    {
      "id": 1,
      "visitor_name": "Maxime Fontaine",
      "visitor_email": "maxime.fontaine@gmail.com",
      "message": "Excellent article...",
      "article_id": 1,
      "created_at": "2025-01-16T09:00:00.000000Z"
    }
  ]
}
```

**Réponse — 404 Not Found**

```json
{
  "message": "No query results for model [App\\Models\\Article] 99"
}
```

---

#### `POST /api/articles`

Créer un nouvel article. L'auteur est automatiquement l'utilisateur connecté.

**Accès** : 🔒 Authentifié

**En-têtes requis**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Corps de la requête** (`multipart/form-data`)

| Champ | Type | Requis | Description |
|---|---|---|---|
| `title` | string | ✅ | Titre (max 191 caractères) |
| `short_description` | string | ✅ | Description courte (max 255 caractères) |
| `content` | string | ✅ | Contenu complet |
| `category_id` | integer | ✅ | ID d'une catégorie existante |
| `image` | file | Non | Image (jpeg/png/jpg/gif, max 2 Mo) |

**Réponse — 201 Created**

```json
{
  "message": "Article créé avec succès avec son image !",
  "article": {
    "id": 13,
    "title": "Mon nouvel article",
    "short_description": "Description courte de l'article",
    "content": "Contenu complet...",
    "image": "https://blog-api-service-fbnq.onrender.com/storage/articles/abc123.jpg",
    "user_id": 1,
    "category_id": 2,
    "created_at": "2025-01-20T14:00:00.000000Z",
    "updated_at": "2025-01-20T14:00:00.000000Z"
  }
}
```

**Réponse — 422 Unprocessable Entity**

```json
{
  "message": "Le titre de l'article est obligatoire.",
  "errors": {
    "title": ["Le titre de l'article est obligatoire."],
    "category_id": ["The selected category id is invalid."]
  }
}
```

---

#### `PUT /api/articles/{id}`

Modifier un article existant. Seul l'auteur de l'article peut le modifier.

**Accès** : 🔒 Authentifié + Propriétaire

> **Note :** Pour les formulaires HTML qui ne supportent pas `PUT` avec upload de fichier, envoyez une requête `POST` avec le champ supplémentaire `_method=PUT`.

**En-têtes requis**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Paramètre d'URL**

| Paramètre | Type | Description |
|---|---|---|
| `id` | integer | ID de l'article à modifier |

**Corps de la requête** (`multipart/form-data`) — tous les champs sont optionnels

| Champ | Type | Description |
|---|---|---|
| `title` | string | Nouveau titre (max 191 caractères) |
| `short_description` | string | Nouvelle description (max 255 caractères) |
| `content` | string | Nouveau contenu |
| `image` | file | Nouvelle image (remplace l'ancienne) |

**Réponse — 200 OK**

```json
{
  "message": "Article et image mis à jour avec succès !",
  "article": {
    "id": 1,
    "title": "Titre modifié",
    "short_description": "Nouvelle description",
    "content": "Nouveau contenu...",
    "image": "https://blog-api-service-fbnq.onrender.com/storage/articles/nouveau.jpg",
    "user_id": 1,
    "category_id": 1,
    "created_at": "2025-01-15T10:00:00.000000Z",
    "updated_at": "2025-01-20T15:30:00.000000Z"
  }
}
```

**Réponse — 403 Forbidden** *(pas propriétaire)*

```json
{
  "message": "This action is unauthorized."
}
```

---

#### `DELETE /api/articles/{id}`

Supprimer un article et son image associée. Seul l'auteur peut supprimer son article.

**Accès** : 🔒 Authentifié + Propriétaire

**En-têtes requis**
```
Authorization: Bearer {token}
```

**Paramètre d'URL**

| Paramètre | Type | Description |
|---|---|---|
| `id` | integer | ID de l'article à supprimer |

**Réponse — 200 OK**

```json
{
  "message": "Article et son image associés supprimés avec succès !"
}
```

**Réponse — 403 Forbidden** *(pas propriétaire)*

```json
{
  "message": "This action is unauthorized."
}
```

---

### 💬 Commentaires

---

#### `POST /api/comments`

Poster un commentaire sur un article.

**Accès** : 🔒 Authentifié

**En-têtes requis**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Corps de la requête** (`application/json`)

```json
{
  "visitor_name": "Jean Dupont",
  "visitor_email": "jean.dupont@exemple.fr",
  "message": "Très bon article, merci pour les explications détaillées !",
  "article_id": 1
}
```

**Règles de validation**

| Champ | Règle |
|---|---|
| `visitor_name` | Requis, chaîne, max 191 caractères |
| `visitor_email` | Requis, format email valide, max 191 caractères |
| `message` | Requis, chaîne, max 1000 caractères |
| `article_id` | Requis, doit exister dans la table `articles` |

**Réponse — 201 Created**

```json
{
  "message": "Commentaire ajouté avec succès !",
  "comment": {
    "id": 24,
    "visitor_name": "Jean Dupont",
    "visitor_email": "jean.dupont@exemple.fr",
    "message": "Très bon article, merci pour les explications détaillées !",
    "article_id": 1,
    "created_at": "2025-01-20T16:00:00.000000Z",
    "updated_at": "2025-01-20T16:00:00.000000Z"
  }
}
```

---

#### `DELETE /api/comments/{id}`

Supprimer un commentaire.

**Accès** : 🔒 Authentifié + Propriétaire

**En-têtes requis**
```
Authorization: Bearer {token}
```

**Paramètre d'URL**

| Paramètre | Type | Description |
|---|---|---|
| `id` | integer | ID du commentaire à supprimer |

**Réponse — 200 OK**

```json
{
  "message": "Commentaire supprimé avec succès !"
}
```

---

### 📊 Tableau de bord

---

#### `GET /api/dashboard`

Récupérer les statistiques globales de la plateforme et les 5 derniers articles.

**Accès** : 🔒 Authentifié

**En-têtes requis**
```
Authorization: Bearer {token}
```

**Réponse — 200 OK**

```json
{
  "stats": {
    "total_articles": 12,
    "total_users": 5,
    "total_comments": 23
  },
  "latest_articles": [
    {
      "id": 12,
      "title": "Les vulnérabilités web les plus exploitées en 2025",
      "short_description": "L'OWASP Top 10 reste la référence...",
      "image": null,
      "user_id": 3,
      "category_id": 6,
      "created_at": "2025-01-15T10:00:00.000000Z",
      "category": {
        "id": 6,
        "name": "Cybersécurité"
      },
      "user": {
        "id": 3,
        "name": "Camille Dubois"
      }
    }
  ]
}
```

---

## Codes d'erreur

| Code | Signification | Cas typique |
|---|---|---|
| `200` | Succès | Requête GET ou DELETE réussie |
| `201` | Créé | POST de création réussi |
| `401` | Non authentifié | Token absent, invalide ou expiré |
| `403` | Interdit | L'utilisateur n'est pas propriétaire de la ressource |
| `404` | Introuvable | ID inexistant dans la base de données |
| `422` | Erreur de validation | Données manquantes ou incorrectes |
| `500` | Erreur serveur | Erreur interne (configuration, BDD) |

---

## Installation locale

### Prérequis

- PHP 8.4+
- Composer
- MySQL ou PostgreSQL
- Node.js (optionnel, pour les assets)

### Étapes

```bash
# 1. Cloner le dépôt
git clone https://github.com/kephas229/Blog-api.git
cd Blog-api

# 2. Installer les dépendances
composer install

# 3. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données dans .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=blog_db
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Créer les tables et insérer les données
php artisan migrate --seed

# 6. Créer le lien symbolique pour les images
php artisan storage:link

# 7. Démarrer le serveur
php artisan serve
```

L'API est accessible sur `http://localhost:8000/api`.

---

## Comptes de test

Ces comptes sont insérés automatiquement par le seeder.

| Nom | Email | Mot de passe | Rôle |
|---|---|---|---|
| Sophie Marchand | `admin@blogflow.fr` | `Admin@2024!` | Administrateur |
| Thomas Leroy | `thomas.leroy@blogflow.fr` | `Thomas@2024!` | Rédacteur |
| Camille Dubois | `camille.dubois@blogflow.fr` | `Camille@2024!` | Rédacteur |
| Lucas Martin | `lucas.martin@blogflow.fr` | `Lucas@2024!` | Rédacteur |
| Elodie Bernard | `elodie.bernard@blogflow.fr` | `Elodie@2024!` | Rédacteur |

---

## Exemples avec cURL

**Connexion**
```bash
curl -X POST https://blog-api-service-fbnq.onrender.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@blogflow.fr","password":"Admin@2024!"}'
```

**Liste des articles**
```bash
curl https://blog-api-service-fbnq.onrender.com/api/articles
```

**Détail d'un article**
```bash
curl https://blog-api-service-fbnq.onrender.com/api/articles/1
```

**Créer un article (authentifié)**
```bash
curl -X POST https://blog-api-service-fbnq.onrender.com/api/articles \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -F "title=Mon article" \
  -F "short_description=Description courte" \
  -F "content=Contenu complet de l'article" \
  -F "category_id=1" \
  -F "image=@/chemin/vers/image.jpg"
```

**Poster un commentaire (authentifié)**
```bash
curl -X POST https://blog-api-service-fbnq.onrender.com/api/comments \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "visitor_name": "Jean Dupont",
    "visitor_email": "jean@exemple.fr",
    "message": "Super article !",
    "article_id": 1
  }'
```

**Statistiques du tableau de bord (authentifié)**
```bash
curl https://blog-api-service-fbnq.onrender.com/api/dashboard \
  -H "Authorization: Bearer VOTRE_TOKEN"
```
