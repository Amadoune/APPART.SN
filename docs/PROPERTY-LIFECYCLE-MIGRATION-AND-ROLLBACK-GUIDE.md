# Property Lifecycle Migration & Rollback Guide

## Migration

Appliquer `017_property_lifecycle_workflow.sql` après les migrations certifiées 001 à 016. La migration crée la table, les contraintes et l'index avec `IF NOT EXISTS`.

## Rollback

Appliquer `017_property_lifecycle_workflow.down.sql`. Le rollback supprime uniquement `real_estate_catalog.property_lifecycle_transitions` et conserve le schéma ainsi que toutes les tables Property historiques.

Le rollback détruit le journal 4.2B ; il doit donc être précédé des procédures de sauvegarde requises hors environnement de test. La campagne PostgreSQL démontre la suppression puis la recréation exactes de la table.
