# Phase 4.7 — Administrative Action Lifecycle Atomic Transaction Policy

## Frontière transactionnelle

`AdministrativeActionLifecycleAtomicTransaction` constitue le port additif. Son
implémentation réutilise `PostgreSqlAggregateOutboxTransaction` et le même PDO
Runtime que le store Lifecycle et le Writer Outbox.

```text
BEGIN
  orchestration
  append journal 034
  mutation miroir historique
  append contexte 035
  inspection exacte
  construction événement et Delivery
  append Outbox
COMMIT
```

Une exception ou un résultat Outbox autre que `Applied` ou `AlreadyApplied`
entraîne `ROLLBACK`. Aucune compensation métier et aucun commit partiel ne sont
autorisés.

## Idempotence et concurrence

- le verrou Lifecycle déterministe sérialise les transitions d'une même action ;
- le rejeu exact retourne `AlreadyApplied` ;
- les identités événementielle et Delivery sont déterministes ;
- l'unicité Outbox converge vers une seule ligne ;
- deux processus identiques convergent vers `Applied + AlreadyApplied` ;
- après concurrence, il existe exactement une transition supplémentaire, un
  contexte et un message Outbox.

## Composition

Le port transactionnel est un alias du singleton transactionnel générique.
L'intégrateur est un singleton paresseux. Aucune transaction, lecture,
inspection ou écriture n'est exécutée au bootstrap. Runtime Health reste à 50
capacités.
