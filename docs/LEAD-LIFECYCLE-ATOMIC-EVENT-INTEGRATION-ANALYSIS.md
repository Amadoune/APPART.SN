# Lead Lifecycle Atomic Event Integration Analysis

## Décision

L'intégration 4.4I réutilise une transaction PostgreSQL unique pour l'orchestration contextuelle Lead et l'écriture dans l'Outbox propriétaire `ContactsLeads`. Elle ne modifie ni le workflow, ni les stores, ni les contrats événementiels et de transport.

La transition exacte n'est jamais reconstruite. Après un résultat `Applied` ou `AlreadyApplied`, elle est relue via `LeadLifecycleContextualReplayInspector`, fondation certifiée 4.4D-R3, dans la transaction active.

## Limites

- aucune lecture de `ListingCatalog` ou `AdvertiserCatalog` ;
- aucune publication directe, consommation, Inbox ou HTTP ;
- aucune horloge, identité ou version implicite ;
- aucune compensation : tout échec déclenche le rollback PostgreSQL.
