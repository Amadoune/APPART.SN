# Phase 4.9 — Gates préventifs

## 4.9A-R1 — Account Status Decision Boundary Amendment

Gate obligatoire avant Workflow. Il fixe :

- la séparation statut, rôles, sessions, credentials, vérifications et
  consentements;
- l'autorité de `Suspend` et `Reactivate`;
- l'effet normatif d'une suspension sur chaque sous-domaine;
- les entrées nécessaires à une décision pure;
- la propriété unique de chaque refus.

Livrable documentaire uniquement. Aucun contrat technique ni code.

## 4.9A-R2 — Replay Attempt Identity Amendment

Gate conditionnel, obligatoire avant Orchestration si les contrats de rejeu
ne prouvent pas ensemble l'identité complète :

```text
accountId + intentId + action + contexte versionné
```

Il sépare absence d'historique, conflit de rejeu et conflit de version.

## 4.9C-R1 — Historical Coexistence Gate

Gate obligatoire avant toute migration. Il démontre comment la nouvelle
persistance coexiste avec `Account`, `AccountRegistry`, sa version et ses
effets historiques, sans double décision ni double écriture partielle.

## 4.9J-R1 — IdentityAccess Outbox Owner Audit

Gate obligatoire avant l'Outbox. Il confirme owner, schéma, conventions
génériques, absence de collision et stratégie de rollback.

Chaque gate reçoit un verdict indépendant. Un gate fermé interdit tous ses
jalons aval.
