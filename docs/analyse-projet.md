# Analyse complète du projet CritiPixel

## Objectif du projet

**CritiPixel** est une plateforme de critiques et de notation de jeux vidéo. Les utilisateurs peuvent :
- S'inscrire et se connecter
- Parcourir un catalogue paginé de jeux avec filtrage/tri
- Soumettre des notes (1-5 étoiles) et des critiques écrites
- Consulter les notes agrégées et la distribution des évaluations

---

## Stack technique

| Composant | Technologie |
|-----------|------------|
| Framework | Symfony 6.4 |
| Langage | PHP ≥ 8.2 (strict types) |
| Base de données | PostgreSQL 16 (Docker) |
| ORM | Doctrine ORM 3.1 |
| Frontend | Twig + Bootstrap 5.3 + Symfony UX Components |
| Upload fichiers | VichUploader 2.3 |
| Extensions Doctrine | Gedmo (slugs automatiques) |
| Tests | PHPUnit (WebTestCase) |
| Données de test | FakerPHP (avec factory française custom) |

---

## Schéma de base de données

### Entités principales

```
USER
├── id (PK)
├── username (UNIQUE)
├── email (UNIQUE)
├── password
└── roles (JSON)

VIDEO_GAME
├── id (PK)
├── title
├── slug (UNIQUE)
├── description (TEXT)
├── release_date (DATE)
├── rating (INT) — note éditoriale
├── average_rating (FLOAT) — moyenne communautaire
├── image_name
├── image_size
├── test (TEXT)
├── numberOfOne (INT) — compteur notes 1★
├── numberOfTwo (INT) — compteur notes 2★
├── numberOfThree (INT) — compteur notes 3★
├── numberOfFour (INT) — compteur notes 4★
└── numberOfFive (INT) — compteur notes 5★

REVIEW
├── id (PK)
├── video_game_id (FK → VIDEO_GAME)
├── user_id (FK → USER)
├── rating (INT 1-5)
└── comment (TEXT)

TAG
├── id (PK)
├── code (UNIQUE)
└── name
```

### Relations

- `USER` 1:N `REVIEW` — un utilisateur écrit plusieurs critiques
- `VIDEO_GAME` 1:N `REVIEW` — un jeu reçoit plusieurs critiques (CASCADE DELETE)
- `VIDEO_GAME` M:N `TAG` — table de jointure `video_game_tags`
- `NumberOfRatingPerValue` est un **Embeddable Doctrine** (dénormalisé dans `video_game`)

---

## Contrôleurs et routes

### AuthController (`/auth`)

| Méthode | Route | Action |
|---------|-------|--------|
| GET/POST | `/auth/login` | Formulaire de connexion |
| GET/POST | `/auth/register` | Inscription utilisateur |
| GET | `/auth/logout` | Déconnexion (géré par Symfony) |

### VideoGameController (`/`)

| Méthode | Route | Action |
|---------|-------|--------|
| GET | `/` | Liste paginée avec filtres et tri |
| GET/POST | `/{slug}` | Page de détail + soumission de critique |

---

## Logique métier : Système de notation

Le `RatingHandler` implémente deux interfaces :

1. **`CalculateAverageRating`** : Calcule la moyenne arrondie au supérieur de toutes les notes d'un jeu
2. **`CountRatingsPerValue`** : Comptabilise combien de notes 1★, 2★, 3★, 4★, 5★ existent pour chaque jeu

Le recalcul est déclenché à chaque nouvelle review soumise.

---

## Système de filtrage et pagination

Architecture en 3 couches :

### Couche 1 : Value Objects (Enums)

- `Direction` : Ascending / Descending → SQL `asc` / `desc`
- `Sorting` : ReleaseDate / Title / Rating / AverageRating → colonnes SQL
- `Page` (readonly DTO) : page, active, label, url
- `Info` (readonly DTO) : count, from, to, total

### Couche 2 : Pagination & Filter

- `Pagination` : page, limit, sorting, direction + `getOffset()`, `getLastPage()`
- `Filter` : search (string optionnel), tags (multi-select, logique "match ALL")

### Couche 3 : Repository & List

- `VideoGameRepository.getVideoGames(Pagination, Filter)` : Paginator avec recherche LIKE + filtrage par tags (sous-requête SQL GROUP BY + HAVING)
- `VideoGamesList` : Encapsule l'appel au repository, construit le formulaire de filtres, génère les liens de pagination

### PaginationValueResolver

ValueResolver custom qui convertit les paramètres HTTP (page, limit, sorting, direction) en objet `Pagination` typé.

---

## Sécurité

### Authentification

- Login par **email** avec `form_login`
- Remember-me : 7 jours
- CSRF activé
- Hashage : bcrypt

### UserListener (Entity Listener)

Hash automatique du `plainPassword` via bcrypt au `@PrePersist`.

### VideoGameVoter

Voter custom pour l'attribut `'review'` :
- Vérifie que l'utilisateur n'a **pas déjà** soumis une critique pour ce jeu
- Utilise `VideoGame.hasAlreadyReview(User)`

### Validation des mots de passe

- `NotCompromisedPassword` : vérifie que le mot de passe n'est pas dans une base de fuites
- `PasswordStrength` : impose un niveau de robustesse minimum

---

## Upload d'images (VichUploader)

- **Répertoire** : `public/images/video_games/`
- **Nommage** : `SmartUniqueNamer` (noms uniques intelligents)
- **Suppression automatique** à la mise à jour ou suppression de l'entité
- **Champs** : `imageName` (stocké en DB), `imageSize` (stocké en DB), `imageFile` (transient)

---

## Composants Twig (Symfony UX)

| Composant | Rôle |
|-----------|------|
| `NavBar` | Navigation + liens auth (login/logout/username) |
| `Card` | Carte de jeu dans la liste (titre, image, tags, notes) |
| `Filter` | Formulaire de filtrage dans la sidebar |
| `Rating` | Affichage étoiles (éditoriale ou communautaire) |
| `Review` | Carte d'une critique individuelle |
| `Progress` | Barre de distribution des notes |
| `Pagination` | Liens de pages (first/prev/pages/next/last) |
| `Info` | "Affichage X à Y sur Z résultats" |
| `Sorting` | Sélection du champ de tri + direction |
| `Alert` | Alertes Bootstrap (success/danger/info) |
| `Tabs` | Onglets (Description / Test / Critiques) |

---

## Templates

### Structure

- **`base.html.twig`** : Layout principal avec NavBar, bloc main, Bootstrap 5 + fonts custom
- **`views/video_games/list.html.twig`** : 9 colonnes (Sorting, Info, grille de Cards, Pagination) + 3 colonnes sidebar (Filter)
- **`views/video_games/show.html.twig`** : 4 colonnes image/ratings + 8 colonnes Tabs (Description, Test, Reviews)
- **`views/auth/login.html.twig`** : Formulaire centré email/password/remember-me/CSRF
- **`views/auth/register.html.twig`** : Formulaire centré username/email/password

### Logique d'affichage des reviews

- L'onglet Reviews affiche le formulaire `ReviewType` uniquement si `is_granted('review', video_game)`
- Distribution des notes via barres de progression pour chaque valeur 1-5

---

## Formulaires

### RegisterType

| Champ | Type | Validation |
|-------|------|-----------|
| username | TextType | NotBlank, Length(max: 30) |
| email | EmailType | NotBlank, Email, NoSuspiciousCharacters |
| plainPassword | PasswordType | NotBlank, NotCompromisedPassword, PasswordStrength |

### ReviewType

| Champ | Type | Validation |
|-------|------|-----------|
| rating | ChoiceType (1-5) | Required, Range(1, 5) |
| comment | TextareaType | Optionnel |

### FilterType (méthode GET, sans CSRF)

| Champ | Type | Validation |
|-------|------|-----------|
| search | TextType | Optionnel |
| tags | EntityType (multi, expanded) | Optionnel, checkboxes |

---

## Tests fonctionnels

### Classe de base : `FunctionalTestCase`

```php
protected function get(uri, params)      // Requête GET
protected function login(email?)         // Login utilisateur
protected function service(className)    // Accès au container
protected function getEntityManager()    // Accès direct à l'EntityManager
```

### Couverture

| Test | Portée |
|------|--------|
| `LoginTest` | Login/logout réussi, erreur d'authentification |
| `RegisterTest` | Inscription valide + assertions DB, data provider pour 6 scénarios de validation |
| `FilterTest` | Pagination (10/page), recherche par titre |
| `ShowTest` | Chargement page de détail avec titre correct |

### Configuration des tests

- DB de test via `DATABASE_URL_TEST` avec suffixe `_test`
- `composer db-test` : Drop → Create → Migrate → Load fixtures
- `composer test` : Exécute db-test + phpunit

---

## Fixtures (données de test)

### UserFixtures

- 10 utilisateurs : `user+0@email.com` à `user+9@email.com`
- Mot de passe : "password" (hashé via UserListener)

### VideoGameFixtures

- 50 jeux vidéo avec contenu Faker
- Titres : "Jeu vidéo 0" à "Jeu vidéo 49"
- Description : paragraphes Faker (10)
- Test : paragraphes Faker (6)
- Note éditoriale : cyclique `(index % 5) + 1`
- Images : "video_game_0.png" (placeholder)

### TODO dans les fixtures

- Ajout de tags aux jeux vidéo
- Ajout de reviews aux jeux vidéo (non encore implémenté)

---

## Infrastructure Docker

```yaml
# docker-compose.yml
services:
  postgres:
    image: postgres:16-alpine
    ports: ["5432:5432"]
    environment:
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: postgres
    volumes:
      - postgres_db:/var/lib/postgresql/data
```

### Utilisation

```bash
docker-compose up -d
export DATABASE_URL="postgresql://postgres:postgres@127.0.0.1:5432/criti_pixel"
composer db  # Setup DB
```

---

## Patterns architecturaux

| Pattern | Implémentation |
|---------|---------------|
| Value Objects comme Enums | `Direction`, `Sorting` avec méthodes `getSql()` |
| Embedded Value Objects | `NumberOfRatingPerValue` dénormalisé dans VideoGame |
| Repository Pattern | Logique de requête complexe dans `VideoGameRepository` |
| Factory Pattern | `ListFactory` crée et injecte les dépendances |
| Voter-Based Authorization | `VideoGameVoter` empêche les critiques dupliquées |
| Entity Listeners | `UserListener` gère le hashing du mot de passe |
| ValueResolver | `PaginationValueResolver` convertit HTTP → objet typé |
| Twig Components | UI modulaire avec props typées |
| Doctrine Embeddables | Objet partagé sans table séparée |

---

## Structure des dossiers (src/)

```
src/
├── Controller/          → Contrôleurs HTTP
├── Doctrine/
│   ├── DataFixtures/    → Données de test
│   ├── EntityListener/  → Listeners Doctrine (hash password)
│   └── Repository/      → Requêtes complexes
├── Faker/               → Factory Faker française custom
├── Form/                → Types de formulaires
├── List/
│   ├── ListFactory.php  → Fabrique de listes
│   └── VideoGameList/   → Liste spécialisée jeux vidéo
├── Model/
│   ├── Entity/          → Entités Doctrine
│   ├── Trait/           → Traits réutilisables
│   └── ValueObject/     → Objets valeur (Pagination, Filter, enums)
├── Rating/              → Logique de calcul des notes
├── Security/
│   └── Voter/           → Voters d'autorisation
└── Twig/
    └── Components/      → Composants Twig UX
```
