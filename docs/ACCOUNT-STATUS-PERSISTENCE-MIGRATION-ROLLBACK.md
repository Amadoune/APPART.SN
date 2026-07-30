# Account Status Persistence — Migration and Rollback

## Migration

```text
041_account_status_lifecycle_workflow.sql
```

La migration :

- crée additivement le schéma `identity_access`;
- crée le journal append-only;
- n'altère aucune table ou migration antérieure;
- ajoute les contraintes de forme, transition, unicité et recherche courante.

## Rollback

```text
041_account_status_lifecycle_workflow.down.sql
```

Le rollback supprime exclusivement la table puis le schéma propriétaire.
Son exécution et la réapplication de la migration sont testées sur PostgreSQL.

Avant activation Runtime, ce rollback ne touche aucune donnée historique.
Après une transition autoritaire, tout rollback fonctionnel exige un
amendement et une stratégie de reconstruction; revenir au booléen historique
est interdit.
