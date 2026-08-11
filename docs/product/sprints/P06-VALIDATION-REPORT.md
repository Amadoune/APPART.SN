# P06 — Validation Report

## Campagnes

| Gate | Résultat |
|---|---|
| Unit + Feature + Architecture ciblés | PASS — 7 tests, 32 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| Vite | PASS — build production généré |
| `git diff --check` | PASS |

## Validation navigateur

| Contrôle | Résultat |
|---|---|
| route Dashboard | PASS — HTTPS, rendu réel |
| H1/H2 et landmark | PASS |
| desktop 1440 | PASS — aucun débordement |
| tablette 820 | PASS — aucun débordement |
| mobile 390 | PASS — aucun débordement |
| console | PASS — aucune erreur ni avertissement |
| données réelles | PARTIAL — source réelle, résultat Empty |
| cartes d'annonces réelles | FAIL — aucune projection publique disponible |
| donnée fictive | PASS — aucune |
| capture PNG | BLOCKED — cible navigateur fermée pendant la capture |

## Historique des validations

Une première exécution Feature a révélé une isolation incorrecte entre deux états de test dans la même méthode. Le test a été séparé en deux cas indépendants ; la campagne terminale est entièrement verte.
