# RC2 Handoff

Ordre requis avant reprise RC2 :

1. ContentSeo Snapshot Materialization Authority : GO ;
2. implémentation productive et validations : GO ;
3. catch-up du Listing RC2 par la capacité productive ;
4. reader ContentSeo : Found ;
5. DecisionTimeReader : Found et cohérent ;
6. inspection read-only de la source Projection ;
7. arrêt à la prochaine divergence.

Le Listing reste Published et SearchDecision reste inchangée.
