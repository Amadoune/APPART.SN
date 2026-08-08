# Legacy Migration & Reconciliation Contracts V1 — Risk Register

| ID | Risque | Maîtrise |
|---|---|---|
| R1 | Contrat pris pour décision de migration | Résultats limités à un statut d'observation |
| R2 | Transfert d'autorité depuis un owner cible | Aucune dépendance vers les domaines cibles |
| R3 | Exposition d'un identifiant Legacy | SubjectKey en entrée seulement, absent des Results |
| R4 | Exposition de PII ou donnée métier | Deux propriétés fermées uniquement |
| R5 | Volumétrie exposée comme vérité | Aucun compteur ou mesure dans les Results |
| R6 | Règle de transformation cachée | Aucune règle, disposition ou payload extensible |
| R7 | Date ambiguë | `LegacyMigrationObservedAt` normalisé UTC microseconde |
| R8 | Catalogue ouvert ou fallback | Enums V1 fermés et cardinalités testées |
| R9 | Mutation via un contrat public | Interfaces limitées à `read` |
| R10 | Couplage à Persistence/Runtime/HTTP | Enclave Application contrôlée par Architecture |
| R11 | Modification d'une capacité gelée | Aucun import ou changement hors LegacyMigration |
