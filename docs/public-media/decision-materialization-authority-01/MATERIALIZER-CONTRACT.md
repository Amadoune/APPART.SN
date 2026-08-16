# Materializer contract

## Completion 01 — contrat final

`MaterializePublicMediaDecisionV2(ListingId)` est le contrat gouvernant. Le caller ne fournit items, ordre, primary, locator, assetVersion, version ou payload. Résultats : Applied, AlreadyApplied, SourceNotReady, ListingMissing, SourceCorrupted, RejectedObsolete, Divergent et DependencyUnavailable.

Le futur nom proposé est `MaterializePublicMediaDecisionV1`, avec `ListingId` comme entrée productive/catch-up. Le caller ne fournit sélection, ordre, primary, URL, version ou payload.

Le contrat reste **non autorisé** tant que l'autorité URL/delivery ne ferme pas source, stabilité, disponibilité et révision. Ses résultats devront être fermés et inclure Applied, AlreadyApplied, NotReady, RejectedObsolete, Divergent et DependencyUnavailable.
