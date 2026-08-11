# F1 — Validation Report

| Gate | Résultat terminal | Preuve |
|---|---|---|
| Unit ciblé | PASS | inclus dans 7 tests, 121 assertions |
| Architecture ciblée | PASS | inclus dans 7 tests, 121 assertions |
| PostgreSQL ciblé | PASS | 1 test, 5 assertions |
| PHPStan ciblé | PASS | 0 erreur |
| Pint ciblé | PASS | aucun changement requis au contrôle terminal |
| git diff --check | PASS | aucune erreur whitespace |

## Cas explicitement validés

- email et téléphone E.164 ;
- credential correct et incorrect ;
- migration bcrypt PHP vers Argon2id après succès seulement ;
- génération et vérification du secret Session ;
- rotation à 30 minutes ;
- expiration idle à 30 minutes ;
- expiration absolue à 8 heures ;
- sélection déterministe de la session la plus ancienne à la sixième admission ;
- replay Session idempotent et optimistic locking PostgreSQL ;
- policy inconnue et snapshot historique incomplet fail-closed.

Les campagnes sont ciblées conformément à la mission. Aucun staging, commit ou tag n'a été effectué.
