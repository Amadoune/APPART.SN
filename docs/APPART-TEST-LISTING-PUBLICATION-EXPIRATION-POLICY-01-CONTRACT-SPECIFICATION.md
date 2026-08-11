# Contract Specification

Statut : `NOT_MATERIALIZED`.

Un futur contrat `ListingPublicationExpirationPolicyV1` ne serait recevable qu'après décision métier explicite. Sa responsabilité pourrait alors être limitée à produire une `ExpirationDate` depuis un instant de publication et des entrées owner-scoped expressément autorisées.

Le présent audit ne fixe ni signature définitive, ni durée, ni configuration. Il confirme seulement les exclusions : aucune décision d'éligibilité, de modération, de média ou de publication ne doit appartenir à cette policy.

Contrat manquant avant implémentation : une décision d'autorité Listing Lifecycle définissant au minimum la durée de publication initiale, le traitement du renouvellement/republication et le caractère fixe ou configurable de la règle.
