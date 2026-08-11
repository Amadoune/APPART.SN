# APPART.TEST LOCAL PRODUCT DATA ORCHESTRATOR 01 — Validation Report

| Validation | Résultat |
|---|---|
| PostgreSQL local | PASS — PostgreSQL 18.4, `appart_test` |
| endpoint Reader | PASS technique — HTTP 200 |
| statut Reader | `empty`, attendu `available` |
| génération active | 0 — FAIL |
| projection courante | 0 — FAIL |
| commande refusée hors local | BLOCKED — commande non créée |
| commande refusée hors `appart_test` | BLOCKED — commande non créée |
| création complète | BLOCKED |
| publication terminale synchronisée | BLOCKED |
| rebuild initial | BLOCKED — `ActiveGenerationMissing` |
| activation | BLOCKED |
| canonicalPath | BLOCKED |
| fiche HTTP 200 | BLOCKED |
| cleanup symétrique | BLOCKED |
| absence de SQL direct | PASS |
| absence de modification Domain / migration / R5 | PASS |

Les campagnes techniques d'une commande inexistante n'ont pas été revendiquées. Le contrôle documentaire et `git diff --check` sont les seules validations finales pertinentes après constat du verrou structurel.
