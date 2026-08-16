# Materializer contract

Nom futur réservé : `MaterializePublicGeographyDecisionV1`.

Entrée minimale candidate : ListingId, permettant de résoudre Property, Address et Place sans accepter un payload client. Sorties fermées candidates : Applied, AlreadyApplied, RejectedObsolete, SourceMissing, SourceCorrupted, Divergent, DependencyUnavailable.

Ce contrat n'est **pas autorisé** tant que le reader de représentation publique owner-side ne fournit pas payload canonique, séquence et causalité.

## Completion 01

Contrat cible versionné : `MaterializePublicGeographyDecisionV2::materialize(ListingId)`. Le caller ne fournit aucun fait Geography. Le materializer compose relation Listing/Property/Place, hierarchy reader, assembler V2 et writer.

Statuts fermés : Applied, AlreadyApplied, RejectedObsolete, SourceMissing, SourceNotReady, SourceCorrupted, Divergent, DependencyUnavailable. Son implémentation reste interdite avant qualification du refresh descendant.
