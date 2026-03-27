# Carnet Moto

App web de suivi d'entretien moto personnelle.

## Stack

- Laravel 13 + Vue 3 + Inertia.js
- PostgreSQL + Redis
- Laravel AI SDK (Anthropic claude-haiku-4-5)
- DomPDF pour l'export
- Déployé sur VPS via Coolify

## Conventions

- UUIDs pour tous les IDs (pas d'auto-increment)
- Soft deletes sur motorcycles et maintenances uniquement
- Toutes les dates en UTC
- Langue de l'interface : français
- Tests : Pest

## Architecture

- app/Ai/Agents/ → agents Laravel AI SDK
- app/Services/ → logique métier
- app/Jobs/ → ProcessInvoiceJob
- app/Console/Commands/ → CheckMaintenanceAlerts

## Schéma BDD

Voir /docs/schema.md
