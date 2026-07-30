# Phase 4.8 — Matrice finale des baselines

## Baseline applicative finale

| Campagne | Résultat certifié |
|---|---:|
| Suite complète | 2 611 / 2 611 — 50 072 assertions |
| Architecture | 540 / 540 — 42 446 assertions |
| Runtime Health | Healthy — 55 capacités |
| Pint | PASS |
| Analyse statique | 0 erreur |

## Baseline PostgreSQL

La dernière baseline PostgreSQL intégralement verte, certifiée lors de 4.8K,
est :

```text
538 / 538 tests
2 281 assertions
```

Pendant 4.8L, deux campagnes ont obtenu `537 / 538`, avec l'unique fluctuation
historique Reservation Lifecycle. Le test concerné repasse isolément
`1 / 1, 3 assertions`. L'autorité a qualifié cette fluctuation comme non
imputable à 4.8L et non bloquante, sans modifier la baseline verte 4.8K ni
certifier le comportement fluctuant.

## Baseline d'entrée

La baseline gelée 4.7 reste la baseline d'entrée historique :

```text
2 463 tests
47 544 assertions
PostgreSQL 521 tests
Architecture 500 tests
Runtime Health Healthy — 50 capacités
```

Les chiffres 4.8 sont additifs et ne remplacent pas la preuve historique 4.7.
