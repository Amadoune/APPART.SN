# Administration Console Owner Reader Foundation — Risk Register

| ID | Risque | Maîtrise |
|---|---|---|
| R1 | Seconde source de décision | Injection exclusive de `AdministrationConsoleOwnerSource` |
| R2 | Fallback silencieux | `match` exhaustifs sans `default` |
| R3 | État incompatible requalifié | Erreur logique explicite, aucune conversion |
| R4 | Agrégation Operator / Queue / Audit | Trois Readers et trois appels source indépendants |
| R5 | Fuite d'un Revision State | Résultats publics limités au statut V1 |
| R6 | Couplage Runtime ou Infrastructure | Tests d'architecture sur le namespace OwnerReader et le Provider |
| R7 | Alias public multiple | Un alias vérifié par contrat public V1 |
| R8 | Provider enregistré plusieurs fois | Enregistrement unique vérifié dans `bootstrap/providers.php` |
| R9 | Régression de migration 082 | Vérification des deux empreintes SHA-256 |
| R10 | Ouverture d'une Foundation ultérieure | Périmètre limité à la Owner Reader Foundation |
