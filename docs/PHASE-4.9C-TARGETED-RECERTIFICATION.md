# Phase 4.9C — Targeted Recertification

## Statut d'entrée

```text
4.9P Historical Account Persistence Foundation
→ GO FINAL
→ FERMÉE
→ GELÉE

Recertification ciblée 4.9C
→ OUVERTE

4.9D Account Status Runtime Composition
→ SUSPENDU
```

## Objet

La recertification vérifie la compatibilité du journal lifecycle 041 avec la
source de production Historical Account sans modifier les fondations gelées.

## Graphe candidat certifié

```text
PostgreSqlAccountStatusWorkflowStore
├─ PDO partagé
├─ AccountRegistry
│  └─ PostgreSqlAccountRepository
│     ├─ même PDO
│     └─ AccountPersistenceMapper
└─ AccountStatusWorkflowMapper
```

Ce graphe est une preuve de compatibilité. Aucun binding
`AccountStatusWorkflowStore` n'est introduit pendant la recertification; sa
composition reste la responsabilité exclusive du futur Sprint 4.9D.

## Matrice de compatibilité

| Critère | Preuve | Verdict |
|---|---|---|
| source Account de production | `AccountRegistry` résout le Repository 4.9P gelé | SATISFAIT |
| PDO partagé | les deux composants reçoivent exactement la même instance | SATISFAIT |
| transaction partagée | les deux participants rejoignent une transaction externe | SATISFAIT |
| rollback transversal | Account, enfants et bootstrap 041 sont annulés ensemble | SATISFAIT |
| version historique séparée | `historical_version` reste portée par Historical Account | SATISFAIT |
| version lifecycle séparée | `version` du journal 041 reste portée par Account Status | SATISFAIT |
| responsabilité lifecycle | seul le store 041 écrit le journal lifecycle | SATISFAIT |
| responsabilité historique | le Repository 4.9P ne lit ni n'écrit le journal 041 | SATISFAIT |
| aucune mutation métier historique | aucun `suspend`, `reactivate` ou `AccountRegistry::save` | SATISFAIT |
| Runtime 4.9P inchangé | aucun binding, contrat ou capacité Health ajouté | SATISFAIT |
| migrations inchangées | 041 et 042 restent gelées | SATISFAIT |
| 4.9D non anticipé | aucun binding Account Status | SATISFAIT |

## Précédence transactionnelle

```text
transaction externe ouverte
→ écriture Historical Account participante
→ bootstrap 041 participant
→ commit ou rollback par l'appelant
```

Ni le Repository Historical Account ni le store 041 ne commitent ou ne
rollbackent la transaction de l'appelant. La même instance PDO rend les
écritures atomiques à l'échelle de cette transaction.

## Fondations non modifiées

- `AccountRegistry`;
- Snapshot V1 et `SensitivePersistenceValueV1`;
- `AccountPersistenceMapper`;
- `PostgreSqlAccountRepository`;
- migrations et rollbacks 041/042;
- Runtime Composition Historical Account;
- Runtime Health à 55 capacités.

## Validations

```text
Compatibilité Architecture ciblée
2 / 2 tests
13 assertions
PASS

Compatibilité PostgreSQL ciblée
2 / 2 tests
9 assertions
PASS

Architecture complète
566 / 566 tests
43 461 assertions
PASS

Suite complète
2 659 / 2 659 tests
51 185 assertions
PASS

PostgreSQL complète
557 / 557 tests
2 364 assertions
PASS

PHPStan
0 erreur

Pint
PASS
```

## Verdict certifié

```text
Recertification ciblée 4.9C
→ GO CERTIFIÉ
→ FERMÉE

4.9D Account Status Runtime Composition
→ OUVERT
```
