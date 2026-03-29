# Schéma de base de données — Carnet Moto

## Vue d'ensemble

7 tables, toutes liées à `motorcycles` (multi-motos ready).
Tous les IDs sont des UUIDs. Toutes les dates sont en UTC.

```
motorcycles
    ├── mileage_logs
    ├── maintenances
    │   ├── maintenance_items ──→ maintenance_types
    │   └── invoices
    ├── maintenance_types
    └── alert_logs ──→ maintenance_types
```

---

## Tables

### `motorcycles`

Informations de la moto. Racine de toute la hiérarchie.

| Colonne           | Type      | Contraintes         | Description           |
| ----------------- | --------- | ------------------- | --------------------- |
| `id`              | uuid      | PK                  |                       |
| `make`            | string    | not null            | Marque (ex: Honda)    |
| `model`           | string    | not null            | Modèle (ex: CB650R)   |
| `year`            | integer   | not null            | Année de fabrication  |
| `plate`           | string    | nullable            | Immatriculation       |
| `initial_mileage` | integer   | not null, default 0 | Kilométrage à l'achat |
| `photo_path`      | string    | nullable            | Photo de la moto      |
| `created_at`      | timestamp |                     |                       |
| `updated_at`      | timestamp |                     |                       |
| `deleted_at`      | timestamp | nullable            | Soft delete           |

---

### `mileage_logs`

Relevés kilométriques manuels. Indépendants des maintenances pour ne pas fausser la chronologie lors d'imports tardifs.

| Colonne         | Type      | Contraintes      | Description                           |
| --------------- | --------- | ---------------- | ------------------------------------- |
| `id`            | uuid      | PK               |                                       |
| `motorcycle_id` | uuid      | FK → motorcycles |                                       |
| `mileage`       | integer   | not null         | Kilométrage relevé                    |
| `logged_at`     | date      | not null         | Date réelle du relevé                 |
| `created_at`    | timestamp |                  | Date de saisie (peut être différente) |

**Index :** `(motorcycle_id, logged_at)`

---

### `maintenances`

Intervention complète sur la moto. Se place sur la chronologie via `mileage` + `performed_at`, indépendamment de la date d'import.

| Colonne                | Type         | Contraintes                 | Description                            |
| ---------------------- | ------------ | --------------------------- | -------------------------------------- |
| `id`                   | uuid         | PK                          |                                        |
| `motorcycle_id`        | uuid         | FK → motorcycles            |                                        |
| `mileage`              | integer      | not null                    | Km au moment de l'intervention         |
| `performed_at`         | date         | not null                    | Date réelle de l'intervention          |
| `garage`               | string       | nullable                    | Nom du garage                          |
| `total_amount`         | decimal(8,2) | nullable                    | Montant total TTC                      |
| `notes`                | text         | nullable                    | Notes libres                           |
| `ai_extraction_status` | enum         | not null, default 'pending' | `pending` `processing` `done` `failed` |
| `created_at`           | timestamp    |                             |                                        |
| `updated_at`           | timestamp    |                             |                                        |
| `deleted_at`           | timestamp    | nullable                    | Soft delete                            |

**Index :** `(motorcycle_id, performed_at)`, `(motorcycle_id, mileage)`

> Le tri chronologique pour l'export PDF se fait sur `mileage` en priorité, puis `performed_at`.

---

### `maintenance_items`

Détail de chaque intervention (vidange, pneu avant, filtre à air…). Une maintenance contient au moins un item.

| Colonne               | Type         | Contraintes                      | Description                            |
| --------------------- | ------------ | -------------------------------- | -------------------------------------- |
| `id`                  | uuid         | PK                               |                                        |
| `maintenance_id`      | uuid         | FK → maintenances                |                                        |
| `maintenance_type_id` | uuid         | FK → maintenance_types, nullable | Null si type libre sans règle d'alerte |
| `label`               | string       | not null                         | Libellé de l'item (extrait ou saisi)   |
| `amount`              | decimal(8,2) | nullable                         | Montant de cet item                    |
| `notes`               | text         | nullable                         | Notes spécifiques à cet item           |
| `created_at`          | timestamp    |                                  |                                        |

**Index :** `(maintenance_id)`, `(maintenance_type_id)`

---

### `maintenance_types`

Types d'entretien configurables avec leurs règles d'alerte. Remplis manuellement au départ.

| Colonne                | Type      | Contraintes            | Description                             |
| ---------------------- | --------- | ---------------------- | --------------------------------------- |
| `id`                   | uuid      | PK                     |                                         |
| `motorcycle_id`        | uuid      | FK → motorcycles       | Rattaché à une moto (multi-motos ready) |
| `name`                 | string    | not null               | Ex: Vidange, Chaîne, Pneu avant         |
| `interval_km`          | integer   | nullable               | Intervalle de récurrence en km          |
| `interval_days`        | integer   | nullable               | Intervalle de récurrence en jours       |
| `alert_threshold_km`   | integer   | nullable               | Alerter X km avant l'échéance           |
| `alert_threshold_days` | integer   | nullable               | Alerter X jours avant l'échéance        |
| `is_active`            | boolean   | not null, default true | Activer/désactiver l'alerte             |
| `created_at`           | timestamp |                        |                                         |
| `updated_at`           | timestamp |                        |                                         |

> Au moins un des deux intervals (`interval_km` ou `interval_days`) doit être renseigné pour qu'une alerte puisse se déclencher. L'un ou l'autre suffit.

**Données initiales suggérées :**

| name                  | interval_km | interval_days | alert_threshold_km | alert_threshold_days |
| --------------------- | ----------- | ------------- | ------------------ | -------------------- |
| Vidange huile         | 6000        | 365           | 500                | 30                   |
| Filtre à huile        | 6000        | 365           | 500                | 30                   |
| Filtre à air          | 12000       | 730           | 1000               | 60                   |
| Chaîne (nettoyage)    | 500         | null          | 100                | null                 |
| Chaîne (remplacement) | 20000       | null          | 1500               | null                 |
| Pneu avant            | null        | null          | null               | null                 |
| Pneu arrière          | null        | null          | null               | null                 |
| Révision générale     | 12000       | 365           | 1000               | 30                   |
| Freins (plaquettes)   | 15000       | null          | 1500               | null                 |
| Liquide de frein      | null        | 730           | null               | 60                   |

---

### `invoices`

Photos de factures associées à une maintenance. Plusieurs photos possibles (recto/verso, plusieurs pages).

| Colonne             | Type      | Contraintes         | Description                      |
| ------------------- | --------- | ------------------- | -------------------------------- |
| `id`                | uuid      | PK                  |                                  |
| `maintenance_id`    | uuid      | FK → maintenances   |                                  |
| `path`              | string    | not null            | Chemin du fichier sur le storage |
| `original_filename` | string    | nullable            | Nom d'origine du fichier uploadé |
| `sort_order`        | integer   | not null, default 0 | Ordre d'affichage                |
| `created_at`        | timestamp |                     |                                  |

**Index :** `(maintenance_id, sort_order)`

---

### `alert_logs`

Historique des alertes Discord envoyées. Permet d'éviter les doublons (pas deux alertes en moins d'une semaine pour le même type).

| Colonne               | Type      | Contraintes                 | Description               |
| --------------------- | --------- | --------------------------- | ------------------------- |
| `id`                  | uuid      | PK                          |                           |
| `motorcycle_id`       | uuid      | FK → motorcycles            |                           |
| `maintenance_type_id` | uuid      | FK → maintenance_types      |                           |
| `channel`             | string    | not null, default 'discord' | Canal d'envoi             |
| `message`             | text      | not null                    | Contenu du message envoyé |
| `sent_at`             | timestamp | not null                    | Date d'envoi              |

**Index :** `(motorcycle_id, maintenance_type_id, sent_at)`

---

## Relations résumées

| Relation                             | Cardinalité       |
| ------------------------------------ | ----------------- |
| motorcycle → mileage_logs            | 1 à N             |
| motorcycle → maintenances            | 1 à N             |
| motorcycle → maintenance_types       | 1 à N             |
| motorcycle → alert_logs              | 1 à N             |
| maintenance → maintenance_items      | 1 à N (minimum 1) |
| maintenance → invoices               | 1 à N             |
| maintenance_type → maintenance_items | 1 à N (nullable)  |
| maintenance_type → alert_logs        | 1 à N             |

---

## Notes d'implémentation Laravel

- Utiliser `HasUuids` sur tous les models
- Soft deletes (`SoftDeletes`) sur `Motorcycle` et `Maintenance` uniquement
- Le tri chronologique du carnet (export PDF) se fait sur `mileage ASC`, puis `performed_at ASC` en cas d'égalité
- La logique d'alerte (`CheckMaintenanceAlerts`) récupère le dernier `maintenance_item` de chaque `maintenance_type` pour calculer le km/jours restants
- `ai_extraction_status` est géré par `ProcessInvoiceJob` : `pending` → `processing` → `done` ou `failed`
