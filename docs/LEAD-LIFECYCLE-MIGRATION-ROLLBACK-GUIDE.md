# Lead Lifecycle Migration & Rollback Guide

## Migration

Appliquer `022_lead_lifecycle_workflow.sql`. La migration crée le schéma propriétaire s'il est absent, la table append-only, ses contraintes et l'index de lecture courante.

## Rollback

Appliquer `022_lead_lifecycle_workflow.down.sql`. Le rollback supprime uniquement `contacts_leads.lead_lifecycle_transitions`. Le schéma et les autres structures propriétaires restent intacts.

Le rollback détruit le journal Lead Lifecycle : il doit être précédé des sauvegardes et contrôles opérationnels appropriés. La réapplication de la migration restaure une structure vide et conforme.
