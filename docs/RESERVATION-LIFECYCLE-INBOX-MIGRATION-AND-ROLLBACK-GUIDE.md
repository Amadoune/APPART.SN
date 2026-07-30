# Reservation Lifecycle Inbox Migration and Rollback Guide

## Migration 020

Appliquer :

`app/Infrastructure/ReservationLifecycleEventRouting/PostgreSql/Migrations/020_reservation_lifecycle_event_inbox.sql`

La migration crée si nécessaire le schéma propriétaire, la table Inbox, ses contraintes et l'index de restauration. Elle ne modifie aucune table existante.

## Rollback

Appliquer :

`app/Infrastructure/ReservationLifecycleEventRouting/PostgreSql/Migrations/020_reservation_lifecycle_event_inbox.down.sql`

Le rollback supprime uniquement `reservation_lifecycle.reservation_lifecycle_event_inbox`. Le schéma et le journal de workflow 019 sont conservés.

## Vérifications

```sql
SELECT to_regclass('reservation_lifecycle.reservation_lifecycle_event_inbox');
SELECT indexname
FROM pg_indexes
WHERE schemaname = 'reservation_lifecycle'
  AND tablename = 'reservation_lifecycle_event_inbox';
```

La campagne PostgreSQL certifie création, suppression, recréation, unicité, restauration exacte, index, concurrence et idempotence transactionnelle.
