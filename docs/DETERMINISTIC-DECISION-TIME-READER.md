# Deterministic Decision Time Reader

`DecisionTimeReader::readByListing()` retourne :

- `Found` avec la valeur durable exacte ;
- `Missing` lorsqu'aucun snapshot Content/SEO n'existe ;
- `Corrupted` lorsque l'identité ou le snapshot est invalide.

`ContentSeoSnapshotDecisionTimeReader` compose le reader de snapshot certifié. `ContentSeoSnapshotDecisionTimeMapper` traduit ses trois états sans branche implicite et sans fabriquer de valeur.

Une absence ou une corruption bloque le futur assemblage de source. Il n'existe aucun fallback vers une horloge, un timestamp de projection ou `updated_at`.
