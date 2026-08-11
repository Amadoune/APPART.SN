# RC1-A File Inventory

## Inventaire du delta

| Catégorie | Modifiés suivis | Nouveaux qualifiés |
|---|---:|---:|
| `app/` | 25 | 55 |
| `src/` | 16 | 106 |
| `tests/` | 17 | 56 |
| `docs/` | 4 | 327 |
| `resources/` | 5 | 13 |
| `public/` | 0 | 2 |
| `config/` | 2 | 0 |
| `bootstrap/` | 1 | 0 |
| `routes/` | 1 | 0 |
| fichiers racine | 2 (`CHANGELOG.md`, `ROADMAP.md`) | 0 |
| **Total** | **73** | **559** |

Les 559 nouveaux fichiers incluent les quatre nouveaux manifestes RC1-A. `RC1-CERTIFICATION-NOTE.md` existait déjà dans le delta documentaire et est remplacé par la décision RC1-A.

## Types des nouveaux fichiers avant livrables RC1-A

- 315 Markdown ;
- 216 PHP ;
- 12 SQL ;
- 8 PNG de preuve ;
- 2 JavaScript ;
- 1 SVG ;
- 1 `robots.txt` ;
- 1 base cache pnpm exclue.

## Tests

- tests suivis modifiés : 17 ;
- nouveaux tests : 56 ;
- campagnes concernées : Unit, Feature, Architecture et PostgreSQL ;
- aucune campagne n'est relancée dans RC1-A conformément au périmètre.

## Documentation et preuves visuelles

Les nouveaux documents retracent les amendements Product, IAM, Media, Listing Lifecycle, Publication Review, Search, projections et preuves locales. Huit PNG existants sont qualifiés comme preuves produit/documentaires et restent inclus. Aucun artefact de build (`dist`, archive, coverage, vendor ou node_modules) n'est présent dans le delta candidat.

## Fichier exclu

| Chemin | Nature | Décision |
|---|---|---|
| `.pnpm-store/v11/index.db` | cache/index local pnpm | EXCLUDED — non-source, non-déterministe, non nécessaire au restore verrouillé |

## Orphelins et composition

- suppression suivie : aucune ;
- Provider PHP présents : 84 ;
- Providers absents de `bootstrap/providers.php` : aucun ;
- tests contenant une qualification de binding/composition : 81 ;
- nouveau Provider non référencé : aucun observé ;
- migration récente sans rollback : aucune.

Conclusion : aucun fichier source orphelin détecté par l'inventaire statique. La preuve runtime complète reste réservée à la campagne post-matérialisation.
