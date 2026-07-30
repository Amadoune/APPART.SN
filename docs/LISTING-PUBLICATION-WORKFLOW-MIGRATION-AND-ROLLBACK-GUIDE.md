# Listing Publication Workflow Migration & Rollback Guide

Migration : `015_listing_publication_workflow.sql`.

Rollback : `015_listing_publication_workflow.down.sql`, limité à `listing_lifecycle.publication_workflow_transitions`.

Avant rollback durable, sauvegarder le journal. Exécuter le fichier down puis vérifier que `to_regclass('listing_lifecycle.publication_workflow_transitions')` retourne NULL. La migration forward recrée table, contraintes et index. Le cycle est testé sur PostgreSQL 18 réel.
