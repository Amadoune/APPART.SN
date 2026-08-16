# Ranking Policy Integration

Le matérialiseur appelle le futur contrat pur SearchDiscovery `PublicSearchRankingPolicyV1`. L'orchestrateur ne contient aucune constante métier.

Sortie normative v1 :

- policyId `public-search-ranking-policy-v1` ;
- `SearchRank::fromInt(0)` ;
- facettes canoniques `[]`.

Une évolution du rang ou des facettes exige une nouvelle version de policy et une nouvelle version de SearchDecision.
