# Reservation Lifecycle Outbox Additive Migration Specification

La migration `021_reservation_lifecycle_outbox_owner.sql`, postérieure à la migration Inbox 020, crée dans `reservation_lifecycle` les tables `public_projection_outbox_messages`, `public_projection_outbox_deliveries`, `public_projection_outbox_cursors` et `public_projection_outbox_replays`.

Elle crée aussi `public_projection_outbox_claim_idx` et `public_projection_outbox_order_idx`. Colonnes, contraintes, clés, valeurs par défaut et types reproduisent la structure générique certifiée de l'owner historique.

Garanties :

- migration 005 inchangée ;
- aucun objet des cinq owners historiques modifié ;
- aucun enregistrement de catalogue ou Worker ;
- application répétable grâce à `IF NOT EXISTS` ;
- rollback limité aux quatre tables, dans l'ordre inverse des dépendances ;
- schéma `reservation_lifecycle` conservé car partagé avec le journal et l'Inbox.
