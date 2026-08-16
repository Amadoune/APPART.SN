# Handoff vers RC2 Iteration 11

## Conditions GO préalables

Avant de reprendre Reopening 04, il faut certifier :

- l'autorité de matérialisation SearchDecision ;
- son implémentation productive owner SearchDiscovery ;
- le handoff Published → décision Search ;
- replay et idempotence ;
- absence de lecture du Projection Store par ce matérialiseur ;
- capacité de rattrapage du Listing Published existant ;
- résultat `Found` du `SearchDecisionReader` pour le Listing RC2.

## Reprise

Le Listing `979cd5aa-ced1-48a1-8adf-8b29c843a0c2` doit être préservé. Sa Queue est `completed` version 4 et le `projectionCommandId` n'a pas été inscrit au ledger puisque l'activation était `NotReady`. Une fois la source légitimement matérialisée, la même intention Projection peut être rejouée selon F3, sans recréer Property, Listing ou parcours Owner.

La reprise doit s'arrêter à la prochaine divergence. Search UX/API reste fermé jusqu'à ce que Projection et son replay soient certifiés.
