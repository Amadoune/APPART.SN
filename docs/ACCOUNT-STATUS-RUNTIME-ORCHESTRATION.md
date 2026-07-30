# Account Status — Runtime Orchestration

## Séquence normative

```text
transaction partagée
→ AccountStatusWorkflowStore::read
→ si LegacyUninitialized : bootstrap
→ nouvelle lecture du journal 041
→ AccountStatusWorkflow::decide
→ AccountStatusWorkflowStore::append
→ résultat applicatif fermé
```

Le store 041 consulte `AccountRegistry` pour l'existence et le bootstrap
historique. Le Repository Historical Account et le store lifecycle utilisent le
même PDO. Lorsqu'une transaction externe existe, tous les participants la
rejoignent sans commit ni rollback local.

## Propriété des décisions

| Résultat | Owner | Origine |
|---|---|---|
| `Applied` | Persistance | append durable accepté |
| `AlreadyInState` | Workflow | état demandé déjà courant |
| `AccountMissing` | Persistance | source Account absente |
| `VersionConflict` | Persistance | versions expected/observed incompatibles |
| `InvalidContext` | Workflow | contexte incompatible avec l'état lu |
| `PersistenceRejected` | Persistance | écriture fermée refusée |
| `PersistenceCorrupted` | Orchestration | lecture ou défaillance durable inutilisable |
| `InspectionCorrupted` | Inspection future | réservé; aucun Inspector n'est inventé en 4.9E |

`InspectionCorrupted` appartient à la surface fermée pour préserver la matrice
certifiée, mais aucun chemin ne le produit tant qu'un contrat d'inspection
versionné n'est pas autorisé.

## Versions

```text
Historical Account → historical_version
Journal 041        → lifecycle_version
```

Le bootstrap peut lire l'état historique initial, mais une transition ne
modifie jamais l'agrégat Account et n'appelle jamais `AccountRegistry::save()`.

## Transaction

`PostgreSqlAccountStatusOrchestrationTransaction` ouvre une transaction
uniquement s'il en est propriétaire. Sinon il rejoint celle de l'appelant. Les
stores participants détectent cette transaction et n'en prennent pas la
propriété.

## Frontière

L'orchestrateur n'introduit aucun Event, Transport, Routing, Inbox, Outbox,
Worker ou HTTP. Il ne modifie aucun contrat 4.9P, Snapshot V1, Repository,
Workflow, store ou migration 041/042.
