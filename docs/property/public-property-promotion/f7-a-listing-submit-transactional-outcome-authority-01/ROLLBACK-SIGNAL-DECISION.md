# Décision du signal de rollback

## Options comparées

| Option | Compatibilité | Coût | Décision |
|---|---|---:|---|
| exception transactionnelle interne | convention `AuthoringOperationRollback` déjà présente | minimal | retenue |
| transaction outcome explicite | exige de modifier le port transactionnel et ses consommateurs | élevé | rejetée |
| rollback-only PDO | couplage infrastructure et résultat difficile à préserver | moyen | rejetée |
| nouvelle primitive | abstraction sans nécessité | élevé | rejetée |

## Mécanisme normatif

L’orchestrateur conserve localement le `ListingPublicationOrchestrationResult`. Si son status n’autorise pas le commit, il déclenche `AuthoringOperationRollback` **dans** la closure. La transaction rollback. L’orchestrateur intercepte ensuite uniquement ce signal local, récupère le résultat conservé et applique le mapping fermé existant.

Une exception technique non accompagnée d’un résultat fermé continue à suivre la réduction `DependencyUnavailable` existante. Refus applicatif et exception technique restent ainsi distingués.

Aucune nouvelle classe, interface ou primitive transactionnelle n’est nécessaire.
