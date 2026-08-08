# Source Provenance Matrix

| Famille | Provenance | Cohérence observée | Décision |
|---|---|---|---|
| `app/Http/**`, `app/Providers/**` | Foundations HTTP/Runtime/Owner Reader certifiées | noms et providers concordent avec les registres | inclure |
| `src/Modules/**` | capacités certifiées 5.4A à 5.8C | modules, contrats, persistence et chaînes aval documentés | inclure |
| `tests/Unit/**` | preuves Unit des Foundations | aucune fixture temporaire identifiée | inclure |
| `tests/Architecture/**` | baselines Architecture stabilisées | modifications ciblées déjà certifiées | inclure |
| `tests/Feature/**` | composition Runtime/HTTP certifiée | aucune collision de stub résiduelle signalée | inclure |
| `tests/PostgreSQL/**` | persistence/outbox et stabilisation concurrence | environnement et assertions stabilisés | inclure |
| `docs/A-5.4*/**`, `docs/PHASE-5.*` | dossiers normatifs historiques | jalons certifiés, fermés ou gelés selon registres | inclure |
| migrations 072–091 et rollbacks | Foundations Persistence/Outbox | fichiers additifs, aucune suppression | inclure, gelés |

Aucun prototype, copie temporaire, duplication abandonnée ou fichier de tooling non versionnable n'a été identifié dans les candidats.
