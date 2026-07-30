# Phase 5.1A — Risk Matrix

Échelle : probabilité et impact 1–5.

| Risque | P | I | Criticité | Gate |
|---|---:|---:|---:|---|
| exposition ou mauvaise vérification du hash | 3 | 5 | 15 | Credential boundary + security tests |
| enumeration email/téléphone | 4 | 5 | 20 | résultat/latence/rate-limit contract |
| brute force distribué | 4 | 5 | 20 | attempt/lockout persistence |
| session fixation/replay | 3 | 5 | 15 | rotation atomique et token hashing |
| recovery token réutilisé | 3 | 5 | 15 | consume + password change transaction |
| double claim email/téléphone | 4 | 5 | 20 | unique reservation + PostgreSQL concurrency |
| double source Account/Profile | 4 | 5 | 20 | seed/cutover authority gate |
| takeover pendant contact change | 3 | 5 | 15 | fresh auth + dual notification |
| Closed assimilé à Suspended | 3 | 5 | 15 | availability policy tests |
| sessions survivant à Closure/password change | 4 | 5 | 20 | atomic invalidation checkpoint |
| PII dans events/revisions | 3 | 5 | 15 | privacy contract tests |
| collision migrations/owners Outbox | 3 | 5 | 15 | owner audit avant persistence/delivery |
| modification Provider/runtime gelé | 3 | 5 | 15 | architecture diff gate |
| hausse implicite Runtime Health 58 | 3 | 4 | 12 | catalog immutability test |
| deletion/anonymization introduite | 2 | 5 | 10 | Erasure exclusion architecture gate |

## Risques de décision à fermer

Avant Contracts :

- remember-me retenu ou exclu ;
- durée/renouvellement/concurrence des sessions ;
- lockout par identité, IP/device ou combinaison ;
- canonicalisation exacte email/téléphone ;
- libération ou conservation permanente des anciennes claims ;
- cooling-off et réouverture Closure ;
- événements réellement justifiés et consumers présents.
