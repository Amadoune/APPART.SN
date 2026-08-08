# Administration Console Event Foundation — Risk Register

| ID | Risque | Maîtrise |
|---|---|---|
| R1 | Source autre qu'un Reader V1 | Une Factory injecte exactement son Reader public V1 |
| R2 | Plusieurs Events pour un résultat | Retour unique par invocation de Factory |
| R3 | Mapping incomplet ou fallback | `match` exhaustif sans `default` |
| R4 | Décision ou état supplémentaire | Réduction homonyme vers un catalogue de même cardinalité |
| R5 | Exposition de la clé sujet | Clé utilisée pour la lecture uniquement, absente du Payload |
| R6 | PII ou révision dans le Payload | Payload limité structurellement à deux propriétés |
| R7 | Date non canonique | Usage exclusif de `AdministrationObservedAt::canonical()` |
| R8 | Couplage HTTP, Runtime ou Infrastructure | Interdiction et test d'architecture |
| R9 | Introduction prématurée de Delivery ou Outbox | Aucun composant ni dépendance associé |
| R10 | Altération de migration 082 | Contrôle des deux empreintes SHA-256 |
