# Worktree Inventory

Jalon : `PHASE-5.9-CANDIDATE-BASELINE-MATERIALIZATION-01`.

## État initial déterministe

- branche : `main` ;
- HEAD initial : `12cf192f1ddb919f01094e026cfba3152dafd582` ;
- fichiers suivis modifiés : 10 ;
- fichiers non suivis individuels : 2 163 ;
- total hors HEAD avant les présents livrables : 2 173 ;
- diff staged initial : vide.

## Inventaire candidat par famille

| Famille | Fichiers initiaux | Classification |
|---|---:|---|
| `app/**` | 118 | `SOURCE_CANDIDATE` |
| `src/**` hors migrations | 879 | `SOURCE_CANDIDATE` |
| `src/**/Migrations/**` | 40 | `MIGRATION_FROZEN` |
| `tests/**` non suivis | 265 | `TEST_CANDIDATE` |
| `docs/**` non suivis | 861 | `DOCUMENTATION_CANDIDATE` |
| suivis modifiés : application/composition | 2 | `SOURCE_CANDIDATE` |
| suivis modifiés : tests | 4 | `TEST_CANDIDATE` |
| suivis modifiés : registres | 4 | `DOCUMENTATION_CANDIDATE` |
| présents livrables du jalon | 11 | `DOCUMENTATION_CANDIDATE` |

Le manifest final contient donc 2 184 chemins hors HEAD : 999 sources, 269 tests, 40 migrations gelées et 876 documents. Aucun fichier n'est supprimé.

## Éléments ignorés pertinents

| Famille | Nombre observé | Classification |
|---|---:|---|
| `vendor/**` | 9 031 | `DEPENDENCY_EXCLUDE` |
| `node_modules/**` | 3 233 | `DEPENDENCY_EXCLUDE` |
| `storage/**` | 5 034 | `LOCAL_ONLY_EXCLUDE` |
| `public/build/**` | 11 | `GENERATED_EXCLUDE` |
| `bootstrap/cache/**` | 3 | `GENERATED_EXCLUDE` |
| `.phpunit.result.cache` | 1 | `TEMPORARY_REVIEW_REQUIRED`, exclu car cache PHPUnit local régénérable |

Aucun `UNKNOWN_BLOCKED` ne subsiste.
