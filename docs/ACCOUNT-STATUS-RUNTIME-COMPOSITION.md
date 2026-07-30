# Account Status — Runtime Composition

## Graphe

```text
AccountStatusWorkflow

AccountStatusWorkflowStore
→ PostgreSqlAccountStatusWorkflowStore
├─ PDO partagé
├─ AccountRegistry
│  └─ PostgreSqlAccountRepository
│     ├─ même PDO
│     └─ AccountPersistenceMapper
└─ AccountStatusWorkflowMapper
```

## Bindings

- `AccountStatusWorkflow` : singleton paresseux;
- `AccountStatusWorkflowMapper` : singleton paresseux;
- `PostgreSqlAccountStatusWorkflowStore` : singleton paresseux;
- `AccountStatusWorkflowStore` : alias unique du Repository concret.

Le port et son implémentation partagent la même instance. Le store 041 et
Historical Account reçoivent le singleton PDO certifié.

## Runtime Health

Deux capacités additives sont introduites :

```text
account_status_workflow
account_status_workflow_store
```

Le catalogue passe de 55 à 57 capacités. Les 55 capacités de la baseline 4.9P
restent présentes et inchangées.

La sonde résout uniquement le graphe de constructeurs. Elle n'appelle jamais
`read`, `bootstrap`, `append`, `find`, `add` ou `save`; elle n'exécute aucune
requête et n'ouvre aucune transaction.

## Frontière

La composition n'ajoute aucune orchestration, mutation métier, migration,
transaction, Event, Transport, Routing, Outbox, Worker ou HTTP. Elle ne modifie
aucune fondation gelée de 4.9P ni les migrations 041/042.
