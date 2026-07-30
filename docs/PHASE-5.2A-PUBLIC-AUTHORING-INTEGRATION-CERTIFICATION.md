# Phase 5.2A — Public Authoring Integration Certification

## Décision d’autorité

**GO CERTIFIÉ — FERMÉ.**

## Preuves terminales

| Campagne | Résultat |
|---|---|
| Unit + Feature + Architecture ciblés | 8 tests, 67 assertions, PASS |
| PostgreSQL 18.x end-to-end ciblée | 1 test, 6 assertions, PASS |
| Architecture complète | 626 tests, 48 114 assertions, PASS |
| Suite applicative | 2 816 tests, 56 333 assertions, PASS |
| Build Vite production | PASS |
| npm audit | 0 vulnérabilité |
| PHPStan | 0 erreur, PASS |
| Pint | PASS |
| `git diff --check` | PASS |

## Scénarios certifiables

- traduction exacte vers `PropertyListingAuthoringOperations` ;
- session requise pour l’API et l’espace UI ;
- auto-scope sans AccountId client ;
- idempotence et champs inconnus refusés ;
- opération inconnue non routable ;
- création end-to-end jusqu’à Listing, draft, ownership et portfolio ;
- assets UI produits par le build de production ;
- aucune dépendance vers Runtime, Persistence ou F-01 dans la façade ;
- aucune extension du catalogue Runtime Health ou des migrations.

La campagne PostgreSQL retenue est la campagne end-to-end ciblée de
l’intégration publique. Aucune campagne PostgreSQL globale n’est revendiquée.
