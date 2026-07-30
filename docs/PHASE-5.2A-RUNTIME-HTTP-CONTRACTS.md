# Phase 5.2A — Runtime & HTTP Contracts V1

## Runtime contract

`PropertyListingAuthoringRuntimeV1` compose les quatre ports propriétaires et
les lectures externes. `inspect()` retourne uniquement :

`Ready`, `MissingBinding(code)`, `DependencyUnavailable(code)` ou
`IncompatibleVersion(code)`.

Il n'ajoute aucune capacité au catalogue Runtime Health F-14 pendant Contracts.

## HTTP contract

| Opération | Méthode conceptuelle | Auth |
|---|---|---|
| initiate Property | POST | owner session |
| patch Property | PATCH | owner |
| create Listing draft | POST | owner |
| get/patch draft | GET/PATCH | owner ou délégation |
| assess completeness | GET | owner ou délégation |
| grant/revoke delegation | PUT/DELETE | owner |
| request submission | POST | permission SUBMIT |
| portfolio | GET | session |

Les URI concrètes restent hors périmètre.

## Résultats HTTP fermés

- mutation appliquée : `200/201` ;
- replay identique : même code et même ressource logique ;
- payload invalide : `422` ;
- conflit de version/intention : `409` ;
- non trouvé ou non autorisé : réponse homogène `404` ;
- dépendance indisponible : `503` sans détail interne.

Toutes les mutations exigent `Idempotency-Key` UUID et version attendue lorsque
la ressource existe. Les réponses privées portent `Cache-Control: no-store`.
