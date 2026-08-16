# F4-A — Validation Report

## Résultats

| Campagne | Résultat | Preuve |
|---|---:|---:|
| Unit, Feature, Architecture F4-A + régressions F1/IAM/Authoring | PASS | 36 tests, 170 assertions |
| PostgreSQL Geography Selection F1 | PASS | 2 tests, 7 assertions |
| PHPStan ciblé, niveau projet | PASS | 0 erreur |
| Pint ciblé | PASS | aucun écart après normalisation ciblée |
| Vite production build | PASS | 6 modules transformés |
| `git diff --check` | PASS | aucun défaut whitespace |

Total PHP exécuté : **38 tests, 177 assertions, 0 échec**.

## Couverture démontrée

- route authentifiée et binding réel F1 ;
- Available, Empty, Missing, Corrupted, DependencyUnavailable et query invalide ;
- DTO minimal et cache `no-store` ;
- unknown fields, texte libre et bornes refusés ;
- rejeu valide, ID absent, mauvais type, mauvais parent, mauvais UUID, curseur divergent et états fermés ;
- contraintes d’architecture et frontière F4 ;
- présence de la preuve et comportement hiérarchique du workspace ;
- régressions Geography Selection F1, IAM Web Entry et Public Authoring.

## PostgreSQL

La campagne PostgreSQL F1 ciblée confirme la lecture réelle, la hiérarchie, la pagination et les états de source nécessaires à la composition HTTP. Aucune migration n’a été créée par F4-A.

## Build

Vite termine avec succès. Les messages relatifs à Fontaine optionnel et aux timings Tailwind sont des avertissements d’outillage non bloquants ; aucune erreur de compilation n’est présente.
