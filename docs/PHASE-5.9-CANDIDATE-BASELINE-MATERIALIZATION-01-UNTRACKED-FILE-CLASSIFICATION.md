# Untracked File Classification

Les 2 163 fichiers non suivis initiaux ont été énumérés avec `git ls-files --others --exclude-standard`.

| Sélecteur exhaustif | Nombre | Classification | Décision |
|---|---:|---|---|
| `app/**/*.php` | 118 | `SOURCE_CANDIDATE` | inclure |
| `src/**/*.php` | 879 | `SOURCE_CANDIDATE` | inclure |
| `src/**/Migrations/*.sql` | 40 | `MIGRATION_FROZEN` | inclure sans modification |
| `tests/**/*.php` | 265 | `TEST_CANDIDATE` | inclure |
| `docs/**/*.md` | 861 | `DOCUMENTATION_CANDIDATE` | inclure |

Contrôles : aucun fichier racine non suivi, aucune extension autre que `.php`, `.md` ou `.sql`, aucun fichier vide, aucune archive, aucun binaire, aucun log, aucun dump et aucun marqueur de conflit. Le mot `Secret` dans les familles SecurityCompliance désigne le domaine certifié et non un secret embarqué.

Les familles ignorées sont exclues par politique et ne sont pas masquées comme sources candidates. Résultat : zéro `UNKNOWN_BLOCKED`.
