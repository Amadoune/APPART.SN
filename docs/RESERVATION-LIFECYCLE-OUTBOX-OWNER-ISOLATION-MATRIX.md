# Reservation Lifecycle Outbox Owner Isolation Matrix

| Situation | `listing_lifecycle` | `reservation_lifecycle` | Lecture |
|---|---:|---:|---|
| Écriture Reservation normale | 0 | 1 | 1 Reservation |
| Même `message_id` dans les deux schémas, copie au mauvais owner | 1 copie invalide | 1 légitime | 1 légitime |
| Écritures Listing et Reservation valides | 1 Listing | 1 Reservation | 2, ordre déterministe |
| Rollback 021 | inchangé | tables Outbox supprimées | owner historique préservé |

L'unicité PostgreSQL s'applique à l'intérieur de chaque owner. L'isolation de lecture est renforcée par la correspondance obligatoire entre le schéma parcouru et `source_module`.
