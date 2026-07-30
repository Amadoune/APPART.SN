# Phase 4.7H-R1 — Administrative Action Lifecycle Outbox Owner Schema Specification

## Migration 037

La migration crée dans `administration_audit` :

1. `public_projection_outbox_messages` ;
2. `public_projection_outbox_deliveries` ;
3. `public_projection_outbox_cursors` ;
4. `public_projection_outbox_replays`.

Les colonnes, contraintes et index sont identiques à l'owner historique `listing_lifecycle`.

## Isolation

* l'écriture est résolue exclusivement depuis `sourceModule` ;
* la lecture filtre strictement `source_module` ;
* aucune restauration croisée entre owners ;
* aucun Writer ou Reader spécifique à Administrative Action.

## Rollback

Le rollback supprime exclusivement les quatre tables créées par 037. Il préserve :

* l'Inbox 036 ;
* le journal Lifecycle 034 ;
* le contexte 035 ;
* la persistance historique AdministrationAudit ;
* les huit autres owners Outbox.
