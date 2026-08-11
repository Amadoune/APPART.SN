# Compatibility Report

## Options comparées

| Option | Ownership | Cohérence workflow/Aggregate | Compatibilité | Décision |
|---|---|---|---|---|
| A — PublicationReview résout les autorités | transfère Media/Property/Lifecycle à l'appelant | transaction distribuée et duplication | incompatible avec F1 et les owners | Rejetée |
| B — Gateway Listing Lifecycle résout les autorités | owner naturel des transitions | transaction locale unique possible | additive, use cases conservés | Retenue |
| C — événements compensatoires ou orchestration ultérieure | ownership différé | divergence temporaire et compensation métier | projection possiblement incohérente | Rejetée |

## Transaction locale

La future Gateway doit partager une transaction PostgreSQL locale entre :

1. réservation du command ledger Lifecycle ;
2. locks workflow et Aggregate ;
3. résolution des autorités en lecture ;
4. mutation Aggregate via le use case existant ;
5. mutation workflow ;
6. sauvegarde Registry et scellement Public Facts ;
7. append des événements/outbox ;
8. complétion du ledger.

Les participants imbriqués utilisent des savepoints et ne commitent jamais une transaction externe. Tout échec rollback l'ensemble.

## Optimistic locking

`expectedVersion` protège le workflow. La version Aggregate est relue et verrouillée par la Gateway, puis transmise au Registry pour son optimistic locking. Une concurrence sur l'un ou l'autre côté annule la transaction entière et retourne `VersionConflict`.

## Compatibilité

- Listing Lifecycle : règles et use cases inchangés ;
- PublicationReview F1 : queue, claims, ledger et migration 096 inchangés ;
- Property/Media : lecture par ports existants, aucune mutation ;
- Public Projection/Search : hors transaction et hors Gateway ;
- historiques divergents : aucun rattrapage implicite ;
- migrations historiques : aucune modification décidée.

Une persistance additive de ledger Lifecycle pourra être nécessaire à l'implémentation future ; elle devra faire l'objet d'une autorisation explicite avec rollback.
