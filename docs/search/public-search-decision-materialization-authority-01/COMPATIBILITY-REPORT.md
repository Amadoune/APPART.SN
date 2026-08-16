# Compatibility Report

| Frontière | Résultat |
|---|---|
| SearchDiscovery ownership | confirmé |
| ListingLifecycle event Published | réutilisable comme trigger, payload inchangé |
| Property / Media | lecture owner-scoped requise, aucune mutation |
| SearchDecision reader/writer/store | compatibles, inchangés |
| Public Projection | inchangée et non lue par le matérialiseur |
| PublicationReview | ne construit pas la décision |
| RC2 Listing | préservé |
| Search UX/API | non ouvert |
| UI NotReady | défaut séparé, non corrigé |
| Migration | aucune requise pour le writer actuel |

Le NO GO protège les owners : accepter une valeur locale comme rank ou une facette de fixture transformerait une commodité de démonstration en règle métier implicite.

## Completion 01

Cette protection reste satisfaite : rang `0` et facettes `[]` proviennent d'une autorité produit certifiée, non des fixtures. Reader, Writer et table restent inchangés ; les nouveaux adapters sont des lectures owner-scoped. Verdict de compatibilité : PASS documentaire.
