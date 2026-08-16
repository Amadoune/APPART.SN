# F3 — Implementation Evidence

## Preuves de code

- `PropertyDecisionOccurredAt` : instant absolu, typé et immutable ;
- `BusinessYearAuthorityV1` : contrat applicatif pur ;
- `BusinessYearResolutionStatus::Resolved` : catalogue fermé minimal ;
- `BusinessYearResolutionResult` : transporte le `BusinessYear` Domain ;
- `UtcCalendarBusinessYearAuthorityV1` : conversion UTC explicite ;
- `BusinessYearAuthorityServiceProvider` : binding réel.

## Vecteurs temporels figés

- `2026-12-31T23:59:59.999999Z` → `2026` ;
- `2027-01-01T00:00:00.000000Z` → `2027` ;
- `2027-01-01T00:30:00.000000+01:00` → `2026` UTC ;
- `2026-12-31T23:30:00.000000-02:00` → `2027` UTC.

Deux représentations du même instant avec offsets différents convergent. Une reconstruction et un replay produisent la même année indépendamment de la date physique d’exécution.

## Compatibilité Domain

`RegisterProperty` et `UpdateProperty` continuent d’accepter exactement le Value Object `BusinessYear` produit. `PropertyTypePolicy` reste propriétaire de `ConstructionYear <= BusinessYear` et continue de rejeter un ConstructionYear futur.

## Pureté

Les contrôles d’architecture excluent persistence, SQL, migration, Repository, ledger, clock, random, Property Authoring, Geography, Projection, Search et HTTP.

PostgreSQL : **NOT_APPLICABLE**.
