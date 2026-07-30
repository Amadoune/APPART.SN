# Phase 4.7C-R2 — Migration 035 & Rollback

## Migration

Exécuter 034 avant 035. La migration 035 crée uniquement le stockage contextuel et son index de lecture courante.

## Rollback

Le rollback 035 supprime uniquement `administrative_action_lifecycle_transition_contexts`.

Il ne modifie ni le journal 034, ni le registre historique, ni les tables d'approbation, de décision et d'audit. Le rollback 034 reste autonome.
