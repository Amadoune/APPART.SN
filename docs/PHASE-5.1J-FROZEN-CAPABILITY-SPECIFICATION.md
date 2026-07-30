# Phase 5.1J — Frozen Capability Specification

Après GO FINAL de 5.1, le gel couvre :

1. les contrats, Commands, Results et invariants 5.1B ;
2. les schémas et migrations 044–054 ;
3. les mappings, stores, snapshots et stratégies de concurrence 5.1C ;
4. le Seed, la normalisation et le cutover 5.1D ;
5. Availability et les providers Runtime 5.1E ;
6. les huit opérations et le journal atomique 5.1F ;
7. les six événements, payloads, transport, routing et delivery 5.1G ;
8. l'Outbox IAM et l'intégration atomique 5.1H ;
9. les endpoints, validations, cookies et politiques HTTP 5.1I ;
10. les campagnes et seuils de qualité consignés par 5.1J.

Toute modification comportementale, contractuelle, SQL, Runtime, Event,
Outbox ou HTTP exige un amendement versionné. Une nouvelle capacité peut
consommer les ports publiés sans amendement si elle reste additive, owner-scoped
et respecte les contrats V1.
