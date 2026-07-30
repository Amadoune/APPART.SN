# Phase 5.0B — Certification Rules

## 1. Processus officiel des Phases 5.x

Chaque phase suit :

1. **Ouverture expresse** : périmètre, owner, contraintes et gate d'entrée.
2. **Discovery** : état réel du dépôt, risques, alternatives, dépendances et
   impact des gels.
3. **Blueprint/contrats** : frontières et preuves attendues avant code.
4. **Réalisation autorisée** : uniquement le périmètre ouvert.
5. **Validation ciblée** : unit, architecture, persistence/runtime selon impact.
6. **Non-régression complète** : qualité et PostgreSQL lorsque requis.
7. **Dossier de certification** : preuves, écarts, risques résiduels, diff.
8. **Prononcé** : GO/NO GO/SUSPENDU par l'autorité.
9. **Clôture et gel** : registres, changelog et prochaine porte.

## 2. Gates minimales

| Gate | Preuves |
|---|---|
| Scope | objectifs/non-objectifs, fichiers autorisés, owner unique |
| Boundary | read/write, dépendances, PII, transaction, idempotence |
| Frozen impact | registre consulté, amendement ouvert si nécessaire |
| Contracts | commands/results/errors/events/version/consumers |
| Persistence | owner schema, mapping, migration/down, concurrence, rollback |
| Runtime | bindings, health, lazy resolution, transaction boundary |
| Delivery | Outbox/inbox, retry, quarantine, replay, observabilité |
| HTTP | authn/authz, validation, status, error/privacy, rate limit |
| Quality | baseline applicable reproduite et comparée |
| Documentation | ADR/spec/matrices/changelog/registers alignés |
| Diff | aucun fichier hors périmètre attribuable à la phase |

## 3. Applicabilité

- Sprint documentaire : architecture, tests applicatifs, PHPStan, Pint,
  Runtime Health non-régression et diff ; PostgreSQL peut hériter de la preuve
  certifiée si aucun SQL/runtime/persistence n'est touché.
- Sprint métier pur : toutes les gates sauf celles explicitement N/A et
  justifiées.
- Persistence/Runtime/Outbox : PostgreSQL complet obligatoire.
- HTTP : Feature/E2E, sécurité et contrats d'erreur obligatoires.
- Projection/consumer : replay, idempotence, divergence et reconstruction
  obligatoires.

## 4. Verdict

Un dossier peut proposer GO seulement si toutes les gates applicables sont
PASS. Il doit proposer NO GO ou SUSPENDU si :

- une dépendance critique ou un owner reste ambigu ;
- une capacité gelée est touchée sans amendement ;
- une commande obligatoire est non exécutée ou non concluante ;
- une régression ou un risque critique reste ouvert ;
- le diff dépasse le périmètre autorisé.

Le dossier formule « GO PROPOSÉ ». Seule l'autorité peut prononcer « GO
CERTIFIÉ ». La proposition ne ferme ni ne gèle automatiquement la phase.

## 5. Règle de prochaine porte

La certification nomme un seul prochain jalon. Aucun travail du jalon suivant
ne commence avant le prononcé. Pour 5.0B :

```text
Si 5.0B → GO CERTIFIÉ et FERMÉE
alors amendement A-5.1-IAM-01 → ouvrable
puis Phase 5.1 → ouvrable uniquement selon la décision d'amendement
```

## 6. Conservation des preuves

Chaque résultat enregistre date, environnement, commande, compte de tests et
assertions, statut et limite éventuelle. Les logs volumineux peuvent rester
hors documentation, mais le résumé ne doit jamais transformer une absence de
preuve en succès.
