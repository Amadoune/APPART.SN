# Validation Report

| Contrôle | Résultat |
|---|---|
| Inventaire configuration/reset | PASS |
| Tests unitaires de garde | PASS — 4 tests, 7 assertions |
| Refus réel avant reset sur collision | PASS — refus attendu |
| Pint ciblé | PASS |
| Création `appart_rebuild` | BLOCKED — privilège PostgreSQL absent |
| Connexion application DB A | NON EXÉCUTABLE |
| Reset test DB B + sentinelle DB A intacte | NON EXÉCUTABLE |
| PostgreSQL représentatif après séparation | NON EXÉCUTABLE |

La garde est effective, mais la séparation matérielle et sa preuve terminale restent incomplètes.

## Reopening 01

| Contrôle | Résultat |
|---|---|
| PostgreSQL local attendu | PASS — 18.4 |
| `appart_test` existe | PASS |
| `appart_rebuild` absente avant création | CONFIRMÉ |
| rôle `appart_test` existe | PASS |
| `appart_test` sans CREATEDB | PASS — `rolcreatedb=false` |
| accès administrateur autorisé disponible | FAIL — absent |
| création DB A, migrations, sentinelle, smoke check | NON EXÉCUTABLE — fail-closed |

Le blocker administratif initial reste inchangé. Aucun contrôle destructif n'a été lancé après ce constat.

## Reopening 02

| Contrôle administratif non destructif | Résultat |
|---|---|
| Rôles et attributs PostgreSQL | PASS — inventoriés |
| Session disponible | `appart_test` uniquement |
| Autorité `postgres` | existe, authentification indisponible |
| pgAdmin | serveur configuré, aucun password sauvegardé |
| `pgpass` / credential Windows / variable admin | absent |
| Création `appart_rebuild` | NON AUTORISÉE — accès admin absent |
| Matérialisation, sentinelle et recertification Resume | NON OUVERTES |

Résultat terminal : STOP fail-closed sans mutation.

## Reopening 03 — certification terminale

| Contrôle | Résultat |
|---|---|
| `appart_rebuild`, owner `appart_test`, UTF8 | PASS |
| `appart_test`, privilèges inchangés | PASS |
| Application `current_database()` | PASS — `appart_rebuild` |
| Tests `current_database()` | PASS — `appart_test` |
| Migrations applicatives | PASS — 81 scripts certifiés, dernière `100_property_promotion.sql` |
| Sentinelle avant/après reset | PASS — fingerprint identique |
| Collision dirigée vers DB A | PASS — refus avant migration/reset |
| PostgreSQL représentatif | PASS — 9 tests, 58 assertions |
| Test post-draft sur DB B | PASS — 1 test, 8 assertions |
| Smoke HTTPS | PASS — Home rendue sans erreur de schéma |
| Ancien draft RC2 reconstruit | NON |

La séparation matérielle est complète et démontrée.
