# Phase 4.6 — Risk Matrix

| Risque | Impact | Gate préventif |
|---|---|---|
| duplication de la règle du média principal | critique | contrat de décision de collection avant orchestration |
| divergence entre snapshot de collection et journal Lifecycle | critique | transaction atomique et version commune avant Runtime |
| rejeu impossible à classifier | élevé | contexte durable et inspecteur exact avant orchestrateur |
| confusion Remove / suppression physique | élevé | `Removed` append-only, aucune suppression SQL |
| événement contenant URL, caption ou source sensible | élevé | payload minimal et politique de confidentialité en 4.6E |
| acquittement implicite | élevé | résultat fermé du routeur en 4.6F et matrice avant Consumer |
| owner Outbox absent | élevé | owner additif `Media → media` avant compatibilité |
| concurrence sur collection et média | critique | verrouillage déterministe et tests multiprocessus |
| dépendance opportuniste à Property | moyen | identité opaque uniquement, aucun appel Property Lifecycle |
