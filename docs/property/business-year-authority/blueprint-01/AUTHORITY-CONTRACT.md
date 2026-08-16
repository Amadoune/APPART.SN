# Authority Contract

## BusinessYearAuthorityV1

Entrée : un Value Object `PropertyDecisionOccurredAt`, instant valide, explicite et immutable.

Sortie : `BusinessYearResolutionResult` fermé contenant :

- statut `Resolved` ;
- `BusinessYear` lorsque résolu.

L'autorité normalise en UTC puis appelle `BusinessYear::fromInt((int) $utc->format('Y'))`. Le propriétaire et HTTP ne fournissent jamais BusinessYear. Les instants mal formés sont rejetés à la construction du Value Object d'entrée, avant l'autorité.
