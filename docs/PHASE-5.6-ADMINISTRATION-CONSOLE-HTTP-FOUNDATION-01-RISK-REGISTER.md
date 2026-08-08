# Administration Console HTTP Foundation — Risk Register

| ID | Risque | Maîtrise |
|---|---|---|
| R1 | Accès direct à l'OwnerSource | Injection exclusive des Readers V1 dans les Controllers |
| R2 | Couplage au Runtime interne | Interdiction et test d'architecture |
| R3 | Couplage PostgreSQL, mapper ou Infrastructure | Interdiction et test d'architecture |
| R4 | Mapping HTTP incomplet | `match` exhaustifs sans `default` |
| R5 | Fallback ou décision supplémentaire | Correspondance fermée statut V1 → code HTTP |
| R6 | Agrégation des trois lectures | Un Controller et un Reader par endpoint |
| R7 | Exposition de données internes | Réponse limitée à `status` |
| R8 | Entrée non déclarée | Rejet des query parameters inconnus |
| R9 | Cache d'une décision sensible | En-tête `Cache-Control: no-store` |
| R10 | Altération de la migration 082 | Contrôle des deux empreintes SHA-256 |
| R11 | Enregistrement ou route multiple | Tests d'unicité du Provider et des routes |
