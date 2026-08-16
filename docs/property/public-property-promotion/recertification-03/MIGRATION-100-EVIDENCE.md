# Preuve migration et ledger 100

La table `real_estate_catalog.property_promotion_commands` porte : commandId UUID primaire, checksum SHA-256, propertyId UUID unique, owner UUID, authoringVersion positif, occurredAt timestamptz et résultat fermé `applied`.

Contraintes auditées : unicité commandId/propertyId, checksum hexadécimal de 64 caractères, version ≥ 1, résultat `applied`, FK Property avec suppression restreinte.

La preuve PostgreSQL persiste une ligne réelle pour la commande `61000000-0000-4000-8000-000000000005`, la Property `61000000-0000-4000-8000-000000000001`, l’owner `61000000-0000-4000-8000-000000000002`, la version 1 et l’instant `2026-08-14T10:00:00Z`; le checksum est celui calculé sur la commande et le snapshot, et le résultat est `applied`.

Le test migration exécute down puis up, confirme la suppression isolée du ledger, la conservation des tables historiques et la réapplication. Aucune migration 101 n’existe.
