# Reservation Lifecycle Outbox Owner Migration and Rollback Guide

## Application

Appliquer les migrations dans l'ordre numérique. La 021 utilise `CREATE SCHEMA IF NOT EXISTS`, crée les quatre tables et deux index, puis doit être vérifiée par les tests PostgreSQL du nouvel owner.

## Rollback et réapplication

Exécuter `021_reservation_lifecycle_outbox_owner.down.sql`. Il supprime exclusivement les tables Outbox Reservation ; il ne supprime ni le schéma, ni le journal Lifecycle, ni l'Inbox de routage. Réappliquer ensuite la migration 021 et vérifier la restauration exacte des structures.

Aucun backfill n'est requis dans ce sprint, puisqu'aucun producteur Reservation ne peut encore écrire dans l'Outbox. Avant un rollback sur un environnement contenant des données, sauvegarder les quatre tables : le script est structurellement réversible, mais la suppression des lignes reste destructive.
