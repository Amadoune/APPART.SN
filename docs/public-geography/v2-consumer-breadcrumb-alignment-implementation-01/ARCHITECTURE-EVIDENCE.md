# Architecture evidence

- Public Geography V2 reste indépendant de Blade/UI.
- ContentSeo reçoit un adapter V2 minimal et distinct.
- Projection transporte les données sans nouvelle décision métier.
- Aucun contrat V2 ne dépend de SearchDiscovery ou de CanonicalUrl.
- Aucune migration V2 n’est présente.
- La page consomme uniquement le read model.

Le test `PublicGeographyV2ConsumerAlignmentArchitectureTest` verrouille ces limites essentielles.
