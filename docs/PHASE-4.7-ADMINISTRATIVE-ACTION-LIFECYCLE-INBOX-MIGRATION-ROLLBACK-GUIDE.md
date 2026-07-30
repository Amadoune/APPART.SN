# Phase 4.7G — Administrative Action Lifecycle Inbox Migration and Rollback

## Migration 036

`036_administrative_action_lifecycle_event_inbox.sql` crée exclusivement :

* l'Inbox `administration_audit.administrative_action_lifecycle_event_inbox` ;
* l'index de reprise `(status, message_id)`.

## Rollback

`036_administrative_action_lifecycle_event_inbox.down.sql` supprime exclusivement cette Inbox.

Le journal 034, le contexte 035 et la persistance historique restent inchangés. Aucune clé étrangère n'empêche un rollback autonome.
