# F4 — Validation Report

Date : 2026-08-14.

## Campagnes

| Campagne | Résultat | Preuve |
|---|---:|---:|
| Unit, Feature, Architecture F4 + régressions F4-A/F1/F2/Authoring/Media/IAM | PASS | 85 tests, 536 assertions |
| PostgreSQL ciblé Authoring, Journey, F1 et Media | PASS | 14 tests, 100 assertions |
| PHPStan ciblé, niveau projet | PASS | 0 erreur |
| Pint ciblé | PASS | aucun écart |
| Vite production build | PASS | 6 modules transformés |
| `git diff --check` | PASS | aucun défaut whitespace |

Total des campagnes PHP terminales sans duplication : **99 tests, 636 assertions, 0 échec**.

## Couverture

- reconstruction legacy et qualification incomplète ;
- huit faits enrichis et persisted ;
- Geography validée, invalide et indisponible ;
- contexte F4-A transporté et champs techniques refusés ;
- première AddressIntent, conservation, deux rotations ;
- checksum stable et divergence ;
- owner, replay, divergence et optimistic locking ;
- migration up/down/réapplication ;
- absence de BusinessYear, AddressId, Aggregate et Promotion ;
- régressions Listing Authoring, Media catalog et IAM boundary.

Les avertissements Vite relatifs à Fontaine optionnel et aux timings Tailwind ne sont pas des erreurs de build.

## Gouvernance

Aucun staging, commit ou tag.
