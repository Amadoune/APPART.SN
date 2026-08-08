# PostgreSQL Cleanup Dependency Diagnostic

Les migrations 090 et 091 partagent le schéma `experience_acceptance`. Le rollback 090 supprime ses tables puis exécute `DROP SCHEMA IF EXISTS experience_acceptance`. Le test OwnerSource exécutait directement ce rollback, alors que les tables `outbox_message_journal` et `outbox_message_state` créées par 091 pouvaient encore exister après les tests Outbox.

PostgreSQL refuse alors la suppression du schéma avec `SQLSTATE[2BP01]`, produisant quatre erreurs de `setUp()`.

Correction minimale : dans le seul setup du test OwnerSource, exécuter le rollback aval 091 avant le rollback 090. Cette correction ne modifie ni migration, ni rollback, ni composant applicatif, ni sémantique métier.
