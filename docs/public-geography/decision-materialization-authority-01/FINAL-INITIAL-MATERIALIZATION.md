# Final initial materialization

Contrat : `MaterializePublicGeographyDecisionV2::materialize(ListingId)`.

Chaîne unique : ListingPublished/catch-up → Listing → Property → Address → terminal Place → hiérarchie root→leaf → assembler V2 → revisionVector → watermark → writer Public Geography.

Le caller ne fournit aucun payload Geography. Initial et catch-up réutilisent exactement le même materializer, sans branche RC2. Résultats fermés : Applied, AlreadyApplied, RejectedObsolete, SourceMissing, SourceNotReady, SourceCorrupted, Divergent, DependencyUnavailable.

Le write est une transaction locale Public Geography. Il n'existe aucune transaction distribuée avec Listing, Property ou Projection.
