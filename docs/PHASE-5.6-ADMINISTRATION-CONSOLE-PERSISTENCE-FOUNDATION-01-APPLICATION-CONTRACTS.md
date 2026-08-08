# Application Contracts

| Stream | Décisions persistables | Résultats de lecture | Résultats d'écriture |
|---|---|---|---|
| Operator | Available, Unavailable | décision, Missing, Corrupted, DependencyUnavailable | Applied, AlreadyApplied, VersionConflict, DivergentRevision, Corrupted, DependencyUnavailable |
| Queue | Ready, Empty | décision, Missing, Corrupted, DependencyUnavailable | mêmes résultats fermés |
| Audit | Available | Available, Missing, Corrupted, DependencyUnavailable | mêmes résultats fermés |

Les Revision States transportent uniquement subject key, révision, décision, effectiveAt et recordedAt.
