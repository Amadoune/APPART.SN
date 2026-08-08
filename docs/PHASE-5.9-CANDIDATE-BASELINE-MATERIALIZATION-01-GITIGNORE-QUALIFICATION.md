# Gitignore Qualification

Le `.gitignore` existant est inchangé.

| Règle/famille | Justification | Classe |
|---|---|---|
| `/vendor`, `/node_modules` | dépendances restaurées depuis les lockfiles | `DEPENDENCY_EXCLUDE` |
| `/public/build`, `/bootstrap/cache` | sorties générées reproductibles | `GENERATED_EXCLUDE` |
| `/storage/**`, `/public/storage`, `/public/hot` | état runtime/local | `LOCAL_ONLY_EXCLUDE` |
| `.env*`, `auth.json`, `storage/*.key` | configuration ou secrets locaux | `SECRET_OR_SENSITIVE_EXCLUDE` |
| caches PHPUnit/IDE/OS | état temporaire poste de travail | `LOCAL_ONLY_EXCLUDE` |

Aucune règle n'est ajoutée pour masquer une source applicative. Les 2 163 fichiers non suivis candidats ne sont couverts par aucune règle d'exclusion.
