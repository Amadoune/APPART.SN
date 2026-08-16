# F3 — Business Year Foundation Implementation 01

## Autorité matérialisée

`BusinessYearAuthorityV1`, owner `RealEstateCatalog Application`, résout exclusivement :

`PropertyDecisionOccurredAt → BusinessYear`

La règle V1 est l’année civile UTC de l’instant métier Property. Elle ne représente ni exercice fiscal, ni année de publication, ni timezone utilisateur ou machine.

## Instant d’entrée

`PropertyDecisionOccurredAt` est un Value Object immutable. Il accepte uniquement une représentation ISO-8601 absolue comportant un offset explicite ou `Z`. Les dates calendaires invalides et les valeurs sans timezone sont rejetées avant l’autorité.

La primitive ne lit jamais l’heure courante et restitue un `DateTimeImmutable`.

## Résolution V1

`UtcCalendarBusinessYearAuthorityV1` :

1. relit l’instant immutable ;
2. applique explicitement `DateTimeZone('UTC')` ;
3. extrait `Y` ;
4. convertit en entier ;
5. construit le Value Object Domain `BusinessYear` ;
6. retourne `Resolved`.

Le résultat fermé ne contient aucun statut artificiel d’indisponibilité ou d’instant invalide.

## Composition

Le binding nominatif relie `BusinessYearAuthorityV1` à `UtcCalendarBusinessYearAuthorityV1` comme singleton applicatif sans Infrastructure.

## Frontières préservées

Aucune modification de Property Authoring, Promotion, `RegisterProperty`, `UpdateProperty`, `PropertyTypePolicy`, Projection ou Search. Aucun SQL, ledger, clock, aléa, HTTP ou migration.
