# Phase 5.0B — Baseline Alignment

## 1. Objet

La baseline officielle au 26 juillet 2026 est l'état consolidé après le GO
certifié de 5.0A. Elle remplace les présentations racine restées en Phase 2.8
sans effacer leur valeur historique.

Ce sprint est exclusivement documentaire. Il ne prononce pas sa propre
certification et n'autorise pas 5.1.

## 2. Hiérarchie des sources

En cas de divergence :

1. décision expresse de l'autorité de certification ;
2. certification finale et amendement versionné certifié ;
3. registres et règles 5.0B ;
4. plan directeur 5.0A ;
5. code, migrations et tests comme preuve de l'état réel ;
6. ADR et spécifications de la capacité concernée ;
7. roadmaps, analyses et blueprints historiques.

Un document historique n'est pas supprimé. Son statut est « archive
contextuelle » dès qu'une référence de rang supérieur le remplace.

## 3. Baseline consolidée

| Dimension | Baseline officielle |
|---|---|
| Architecture | monolithe modulaire DDD, PHP 8.5/Laravel 13/PostgreSQL 18.x |
| Domaines | 12 modules implémentés + LegacyMigration temporaire |
| Aggregates historiques | 14 |
| Migrations | 001 à 043, owners explicités dans les certifications |
| Lifecycles complets | 9, certifiés et gelés |
| Projection publique | durable, reconstruisible, réconciliable, HTTP |
| Runtime Health | `Healthy`, 58 capacités |
| Qualité applicative | 2 733 tests, 52 475 assertions |
| Phase close la plus récente | 5.0A, GO CERTIFIÉ |
| Phase ouverte | 5.0B seulement |
| Phase future conditionnée | 5.1 après GO 5.0B + amendement Account requis |

## 4. Documents réalignés

| Document | Divergence antérieure | Alignement 5.0B |
|---|---|---|
| `README.md` | Phase 2.8 et quatre repositories seulement | statut 5.0, gels, qualité et gouvernance |
| `ROADMAP.md` | cinquième Registry annoncé comme prochaine porte | pointeur vers roadmap 5.0 certifiée |
| `CHANGELOG.md` | chronologie arrêtée à 2.8 | entrées 5.0A et 5.0B ajoutées |
| `MASTER-BLUEPRINT.md` | proposition Sprint 2 | vision conservée, autorité 5.0 explicitée |

## 5. Référence documentaire unique

La baseline se lit comme un ensemble atomique :

- `PHASE-5.0B-BASELINE-ALIGNMENT.md` : état ;
- `PHASE-5.0B-GOVERNANCE.md` : autorité et changement ;
- `PHASE-5.0B-FROZEN-CAPABILITIES-REGISTER.md` : gels ;
- `PHASE-5.0B-AMENDMENT-REGISTER.md` : exceptions versionnées ;
- `PHASE-5.0B-QUALITY-BASELINE.md` : preuves reproductibles ;
- `PHASE-5.0B-CERTIFICATION-RULES.md` : gates 5.x ;
- les six livrables 5.0A : carte et trajectoire métier.

## 6. Divergences résiduelles acceptées

Les anciens chiffres de tests, états « proposés » et prochaines étapes restent
dans les certifications et changelogs historiques. Ils ne constituent pas une
divergence active dès lors qu'ils sont datés et subordonnés par la hiérarchie
ci-dessus.

Il n'existe plus de divergence documentaire racine majeure connue.
