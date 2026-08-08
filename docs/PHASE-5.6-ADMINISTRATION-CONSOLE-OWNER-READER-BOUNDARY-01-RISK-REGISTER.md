# Administration Console Owner Reader — Risk Register

| ID | Risque principal | Impact | Maîtrise exigée |
|---|---|---|---|
| R1 | Introduction d'une seconde source | Divergence d'autorité | Dépendance exclusive à `AdministrationConsoleOwnerSource` |
| R2 | Fallback sur un état par défaut | Décision inventée | Mapping exhaustif sans branche de fallback |
| R3 | Agrégation des trois streams | Nouvelle décision métier | Trois lectures et trois réductions strictement indépendantes |
| R4 | Confusion entre Runtime et décision | Statut métier dérivé d'une disponibilité technique | Runtime et Runtime Read exclus |
| R5 | Couplage direct à PostgreSQL ou SQL | Contournement du port Application | Dépendance au port source uniquement |
| R6 | Fuite d'un Revision State | Exposition de métadonnées internes | Résultats V1 limités au statut |
| R7 | Modification d'un catalogue V1 | Rupture d'une baseline certifiée | Correspondance homonyme sans contrat supplémentaire |
| R8 | Réouverture de la migration 082 | Altération d'une Persistence certifiée | Migration explicitement inchangée et interdite |
| R9 | Implémentation pendant l'audit | Dépassement du jalon documentaire | Livrables limités aux documents autorisés |
| R10 | Ouverture implicite d'une Foundation ultérieure | Chevauchement de jalons | Certification proposée pour l'audit seulement |

Le risque résiduel principal relève de la future implémentation : elle devra démontrer que tout ajout d'état provoque une adaptation explicite du mapping et ne peut être absorbé silencieusement.
