# F3 — Certification Note

## Verdict

**GO PROPOSÉ — F3 BUSINESS YEAR FOUNDATION IMPLEMENTATION 01.**

## Motif

`BusinessYearAuthorityV1` est exécutable et dérive exclusivement l’année civile UTC du `PropertyDecisionOccurredAt` stable. Les frontières du 31 décembre et du 1er janvier sont exactes ; des offsets représentant le même instant convergent ; le replay conserve l’année de l’instant métier original.

Le `BusinessYear` Domain existant est réutilisé. `RegisterProperty`, `UpdateProperty` et `PropertyTypePolicy` restent inchangés et compatibles. Aucune valeur BusinessYear client n’est introduite.

L’autorité n’a aucune persistence, migration, clock courante, configuration mutable, ledger, source aléatoire, Geography, Projection, Search ou HTTP.

## Gouvernance

F3 peut être fermée GO. F4 Authoring Completeness, F5 Source Completeness Recertification, F6 Promotion et RC2 Iteration 11 restent non ouvertes. Aucun staging, commit ou tag.
