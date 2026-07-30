# Phase 5.2B — PostgreSQL Certification

## Statut

**PASS — preuve terminale certifiée.**

## Campagne définie

`PostgreSqlMediaIngestionPersistenceTest` contient quatre scénarios :

1. round-trip et convergence des quatre owners ;
2. replay d’un intent historique après versions ultérieures ;
3. optimistic locking sans intent partiel ;
4. réservation et reconstruction du journal Attachment.

## Exécution terminale

- campagne ciblée : **4 tests, 24 assertions, PASS** ;
- campagne PostgreSQL complète : **600 tests, 2 608 assertions, PASS**.

Le défaut initial provenait de la sérialisation d’un payload PHP vide en tableau
JSON `[]`, incompatible avec la contrainte objet de la migration 058. Le cast
`(object)` produit désormais `{}`. Aucune instrumentation temporaire ne
subsiste.
