# 🛡️ SignalGabon

**Plateforme officielle de signalement de la corruption au Gabon**  
Commission Nationale de Lutte Contre la Corruption et les Infractions Économiques (CNLCEI)

---

## Architecture

```
signal-gabon/
├── backend/          → API REST Laravel 11 + SQLite + Sanctum
├── citizen-app/      → Application citoyenne React (mobile-first + desktop)
├── admin-app/        → Interface agents CNLCEI React (desktop)
└── README.md
```

---

## Prérequis

- PHP >= 8.2 + Composer
- Node.js >= 18 + npm
- Git

---

## Installation

### 1. Backend Laravel

```bash
cd backend
cp .env.example .env          # Configurer DB_DATABASE avec le chemin absolu vers database.sqlite
touch database/database.sqlite
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve              # http://localhost:8000
```

### 2. Application citoyenne

```bash
cd citizen-app
npm install
npm run dev                   # http://localhost:5173
```

### 3. Interface admin

```bash
cd admin-app
npm install
npm run dev                   # http://localhost:5174
```

---

## Comptes de démonstration

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Superviseur | superviseur@cnlcei.ga | Admin1234! |
| Agent | jean.mboumba@cnlcei.ga | Agent1234! |
| Agent | marie.ondo@cnlcei.ga | Agent1234! |
| Agent | pierre.nzoghe@cnlcei.ga | Agent1234! |
| Agent | sylvie.bekale@cnlcei.ga | Agent1234! |
| Agent | antoine.moussavou@cnlcei.ga | Agent1234! |

---

## Endpoints API principaux

### Public (sans authentification)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/signalements` | Soumettre un signalement |
| GET | `/api/signalements/{code}/statut` | Consulter le statut d'un dossier |
| POST | `/api/signalements/{code}/preuves` | Ajouter des preuves |
| GET | `/api/stats/publiques` | Statistiques publiques |

### Auth

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/auth/login` | Connexion agent |
| POST | `/api/auth/logout` | Déconnexion |
| GET | `/api/auth/me` | Profil agent connecté |

### Admin (auth:sanctum requis)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/admin/signalements` | Liste paginée (filtres: statut, province, agent_id) |
| GET | `/api/admin/signalements/{id}` | Détail d'un signalement |
| PATCH | `/api/admin/signalements/{id}` | Modifier statut / agent / message |
| GET | `/api/admin/stats` | Statistiques complètes |
| GET | `/api/admin/agents` | Liste agents actifs |

---

## Fonctionnalités

### Application citoyenne
- Formulaire de signalement anonyme en 4 étapes
- Upload de preuves (images, PDF, audio)
- Suivi de dossier par code unique (SG-XXXX-XXXX)
- Timeline visuelle des statuts
- Statistiques publiques
- Interface mobile-first + desktop responsive

### Interface admin (CNLCEI)
- Tableau de bord avec KPIs et graphiques (Recharts)
- Gestion des signalements avec filtres
- Attribution des dossiers aux agents
- Messagerie citoyenne (visible publiquement)
- Statistiques avancées par agent (superviseur)
- Authentification sécurisée (Sanctum, token 8h)

---

## Statuts des dossiers

| Statut | Description |
|--------|-------------|
| `recu` | Signalement reçu, en attente d'examen |
| `en_examen` | En cours d'examen par la CNLCEI |
| `attribue` | Attribué à un agent |
| `en_instruction` | En cours d'instruction |
| `traite` | Traité, mesures prises |
| `classe` | Classé sans suite |

---

## Licence

MIT — République Gabonaise / CNLCEI — 2024
