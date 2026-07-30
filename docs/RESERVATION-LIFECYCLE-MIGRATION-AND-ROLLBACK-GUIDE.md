# Reservation Lifecycle Migration & Rollback Guide

Migration : `019_reservation_lifecycle_workflow.sql`.

Elle crée le schéma propriétaire si nécessaire, la table append-only, ses contraintes et l'index de lecture courante. Elle est idempotente au niveau DDL.

Rollback : `019_reservation_lifecycle_workflow.down.sql`.

Il supprime exclusivement `reservation_lifecycle.reservation_lifecycle_transitions`. Le schéma n'est pas supprimé afin de ne pas préjuger de ses usages futurs. Avant un rollback durable, sauvegarder le journal. Le test PostgreSQL exécute successivement rollback et migration puis vérifie `to_regclass`.
