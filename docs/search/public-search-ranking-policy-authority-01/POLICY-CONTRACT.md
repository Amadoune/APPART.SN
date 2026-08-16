# Policy Contract

Contrat documentaire futur : `PublicSearchRankingPolicyV1`.

Input minimal : état et révision Listing versionnés. Les faits Property et Media restent fournis à la composition globale pour la visibilité et `SourceRevisionSet`, sans influencer la sortie ranking v1.

Output conceptuel :

- `SearchRank(0)` ;
- facettes canoniques `[]` ;
- policy version `public-search-ranking-policy-v1`.

Le contrat est pur, déterministe, sans persistance, Projection, UI, clock ou random. Aucun contrat PHP n'est créé ici.
