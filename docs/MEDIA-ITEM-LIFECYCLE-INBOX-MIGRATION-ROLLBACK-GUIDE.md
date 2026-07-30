# Media Item Lifecycle Inbox Migration and Rollback Guide

## Migration

Appliquer `033_media_item_lifecycle_event_inbox.sql` après les migrations 031 et 032. La migration crée uniquement l'Inbox et son index de reprise dans le schéma `media`.

## Rollback

Appliquer `033_media_item_lifecycle_event_inbox.down.sql`. Le rollback supprime uniquement `media.media_item_lifecycle_event_inbox`.

Les journaux 031 et 032 restent intacts. Aucune clé étrangère ne lie l'Inbox à ces fondations, ce qui garantit l'autonomie du rollback.
