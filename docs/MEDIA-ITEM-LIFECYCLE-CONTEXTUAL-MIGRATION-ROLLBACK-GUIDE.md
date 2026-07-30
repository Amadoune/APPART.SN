# Media Item Lifecycle Contextual Migration and Rollback Guide

## Migration

Appliquer `032_media_item_lifecycle_context.sql` après 031. La migration crée uniquement la table contextuelle et son index de recherche par collection.

## Rollback

Appliquer `032_media_item_lifecycle_context.down.sql`. Il supprime exclusivement la table 032.

Le journal `media.media_item_lifecycle_transitions`, la migration 031 et les autres schémas restent inchangés. Le rollback autonome de 031 demeure possible, car 032 n'introduit aucune clé étrangère vers le journal historique.
