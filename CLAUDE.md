# CLAUDE.md — Carnet Moto

## Contexte du projet

Application web personnelle de suivi d'entretien moto. Permet d'importer des photos de factures,
d'en extraire les données via IA, de suivre l'historique des interventions et de recevoir des
alertes Discord quand un entretien approche.

URL de production : https://carnet-moto.axelduquelzar.fr

---

## Stack technique

- **Framework** : Laravel 13
- **Frontend** : Vue 3 + Inertia.js (mobile-first, pas d'API REST séparée)
- **Base de données** : PostgreSQL
- **Cache / Queue** : Redis (driver queue + cache)
- **IA** : Laravel AI SDK (`laravel/ai`) + Anthropic claude-haiku-4-5
- **PDF** : DomPDF
- **Tests** : Pest
- **Hébergement** : VPS Coolify (Docker)

---

## Conventions de code

- UUIDs pour tous les IDs (`HasUuids` sur tous les models)
- Soft deletes (`SoftDeletes`) sur `Motorcycle` et `Maintenance` uniquement
- Toutes les dates stockées en UTC
- Langue de l'interface : français
- Nommage des branches : `feature/SCRUM-XX-description`
- Un commit par US terminée : `feat(SCRUM-XX): description`

---

## Architecture des dossiers

```
app/
├── Ai/
│   └── Agents/
│       └── InvoiceExtractorAgent.php     # Agent Laravel AI SDK (structured output)
├── Console/
│   └── Commands/
│       └── CheckMaintenanceAlerts.php    # Planifié quotidiennement
├── Http/
│   └── Controllers/
│       ├── DashboardController.php
│       ├── MaintenanceController.php
│       ├── MileageController.php
│       ├── ExportController.php
│       └── SettingsController.php
├── Jobs/
│   └── ProcessInvoiceJob.php             # Dispatch après upload, appelle l'agent IA
├── Models/
│   ├── Motorcycle.php
│   ├── Maintenance.php
│   ├── MaintenanceItem.php
│   ├── MaintenanceType.php
│   ├── MileageLog.php
│   ├── Invoice.php
│   └── AlertLog.php
├── Notifications/
│   └── MaintenanceDueNotification.php   # Canal Discord webhook
└── Services/
    ├── AlertService.php                  # Calcul km/jours restants, anti-doublon
    ├── InvoiceExtractionService.php      # Orchestration upload → agent → sauvegarde
    └── PdfExportService.php              # Génération PDF via DomPDF
```

---

## Schéma de base de données

Voir `/docs/schema.md` pour le détail complet.

Tables (toutes liées à `motorcycles`, multi-motos ready) :

| Table               | Rôle                                                                    |
| ------------------- | ----------------------------------------------------------------------- |
| `motorcycles`       | Informations moto, racine de la hiérarchie                              |
| `mileage_logs`      | Relevés km manuels, indépendants des maintenances                       |
| `maintenances`      | Intervention complète (mileage + performed_at = position chronologique) |
| `maintenance_items` | Détail de chaque intervention (N items par maintenance)                 |
| `maintenance_types` | Types d'entretien avec règles d'alerte (interval_km / interval_days)    |
| `invoices`          | Photos de factures (N photos par maintenance)                           |
| `alert_logs`        | Historique des alertes Discord envoyées (anti-doublon)                  |

**Règle critique** : le tri chronologique se fait sur `mileage ASC` puis `performed_at ASC`.
Une facture importée tardivement se place automatiquement au bon endroit via ces deux champs.

---

## Gestion des tickets Jira

**Projet** : `SCRUM` sur https://axelduquelzar.atlassian.net

Pour chaque US à implémenter, tu peux consulter le ticket Jira correspondant.
Lorsqu'une US est terminée et les tests passent, mets le ticket en statut "Done".

### Backlog complet par sprint

#### Sprint 1 — Fondations (EPIC 1 + EPIC 3 CRUD + EPIC 7)

| Ticket   | US    | Description                                                                                      |
| -------- | ----- | ------------------------------------------------------------------------------------------------ |
| SCRUM-8  | US-01 | Connexion sécurisée (email/mdp, session persistante, pas de register public, compte créé en CLI) |
| SCRUM-9  | US-02 | Configuration moto (marque, modèle, année, immatriculation, km initial, photo optionnelle)       |
| SCRUM-13 | US-06 | Visualiser l'historique des entretiens (liste chronologique, tri mileage ASC)                    |
| SCRUM-14 | US-07 | Détail d'une intervention (tous champs + items + photos factures + modifier/supprimer)           |
| SCRUM-15 | US-08 | Saisie manuelle d'une intervention (sans facture, date et type obligatoires)                     |
| SCRUM-16 | US-09 | Modifier une intervention (tous champs éditables, remplacement photo)                            |
| SCRUM-17 | US-10 | Supprimer une intervention (confirmation obligatoire, suppression fichiers associés)             |
| SCRUM-23 | US-16 | Dashboard principal (km actuel, dernière intervention, widget alertes, raccourcis)               |
| SCRUM-24 | US-17 | Paramètres (webhook Discord + test, types d'entretien, infos moto)                               |

#### Sprint 2 — Import IA (EPIC 2)

| Ticket   | US    | Description                                                                               |
| -------- | ----- | ----------------------------------------------------------------------------------------- |
| SCRUM-10 | US-03 | Upload photo facture (JPG/PNG, max 10 Mo, preview avant confirmation, mobile natif)       |
| SCRUM-11 | US-04 | Extraction automatique par IA (date, km, type, montant, garage via InvoiceExtractorAgent) |
| SCRUM-12 | US-05 | Correction et validation extraction (formulaire pré-rempli éditable, notes libres)        |

#### Sprint 3 — Kilométrage + Alertes (EPIC 4 + EPIC 5)

| Ticket   | US    | Description                                                                                |
| -------- | ----- | ------------------------------------------------------------------------------------------ |
| SCRUM-18 | US-11 | Saisir km actuel (saisie libre, historique conservé, validation km croissant)              |
| SCRUM-19 | US-12 | Configurer rappel entretien (interval_km ET/OU interval_days, seuil d'alerte, types perso) |
| SCRUM-20 | US-13 | Alerte Discord (webhook, message formaté km/jours restants, max 1/semaine par type)        |
| SCRUM-21 | US-14 | Entretiens à venir dashboard (widget code couleur vert/orange/rouge)                       |

#### Sprint 4 — Export + Finitions (EPIC 6)

| Ticket   | US    | Description                                                                     |
| -------- | ----- | ------------------------------------------------------------------------------- |
| SCRUM-22 | US-15 | Export PDF (en-tête moto, liste chronologique, récap km+montant, option photos) |

---

## Comportement de l'agent IA (InvoiceExtractorAgent)

L'agent reçoit une image de facture en base64 et retourne un JSON structuré :

```php
// Output schema attendu
[
    'performed_at'  => 'string (date Y-m-d)',
    'mileage'       => 'integer|null (kilométrage relevé sur la facture)',
    'garage'        => 'string|null (nom du garage)',
    'total_amount'  => 'float|null (montant TTC total)',
    'items'         => [
        [
            'label'  => 'string (libellé de la prestation)',
            'amount' => 'float|null (montant de cet item)',
        ]
    ],
    'confidence'    => 'string (high|medium|low — niveau de confiance global)',
]
```

Le `ProcessInvoiceJob` orchestre : upload → sauvegarde Invoice → dispatch job →
agent IA → création Maintenance + MaintenanceItems → statut `done` ou `failed`.

---

## Logique d'alerte (AlertService)

Pour chaque `MaintenanceType` actif avec `is_active = true` :

1. Récupérer le dernier `MaintenanceItem` lié à ce type (via `maintenance.performed_at DESC`)
2. Calculer km restants : `(last_mileage + interval_km) - current_mileage`
3. Calculer jours restants : `(last_performed_at + interval_days) - today`
4. Si km_restants <= alert_threshold_km OU jours_restants <= alert_threshold_days → alerte
5. Vérifier `alert_logs` : pas d'envoi si déjà alerté il y a moins de 7 jours pour ce type
6. Envoyer webhook Discord + créer AlertLog

Commande planifiée : `php artisan maintenance:check-alerts` (quotidien via Laravel Scheduler)

---

## Variables d'environnement nécessaires

```env
APP_NAME="Carnet Moto"
APP_URL=https://carnet-moto.axelduquelzar.fr

DB_CONNECTION=pgsql
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

ANTHROPIC_API_KEY=
AI_DEFAULT_PROVIDER=anthropic
AI_DEFAULT_MODEL=claude-haiku-4-5

DISCORD_WEBHOOK_URL=   # Configurable aussi depuis les Settings de l'app
```

---

## Commandes utiles

```bash
# Créer un utilisateur admin
php artisan tinker
> User::create(['name' => 'Axel', 'email' => 'axel@...', 'password' => bcrypt('...')])

# Lancer les alertes manuellement
php artisan maintenance:check-alerts

# Lancer les tests
php artisan test

# Worker de queue (dev)
php artisan queue:work
```

## Développement local

Utiliser **Laravel Sail** pour tous les services locaux (PostgreSQL + Redis via Docker).

```bash
./vendor/bin/sail up -d       # démarrer
./vendor/bin/sail down        # arrêter
./vendor/bin/sail artisan ... # commandes artisan
./vendor/bin/sail composer ... # composer
```

- `APP_ENV=local` → pas besoin de `--force` sur les migrations
- DB : `pgsql:5432`, user `sail`, password `password`, base `carnet_moto`
- Ne jamais lancer `php artisan` directement — toujours passer par `sail artisan`

## Schéma BDD

Voir /docs/schema.md
