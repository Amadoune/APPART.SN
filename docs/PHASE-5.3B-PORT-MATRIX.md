# Phase 5.3B — Application Port Matrix

## Ports propriétaires

| Port | Owner | Responsabilité | Entrées / sorties |
|---|---|---|---|
| `ModerationCaseStore` | ModerationReports | charger et sauvegarder l'état propriétaire d'un dossier | état contractuel, version, résultat fermé |
| `ModerationQueueStore` | ModerationReports | lire et réserver les items de la projection | critères fermés, lease, résultats fermés |
| `ModerationDecisionStore` | ModerationReports | lire la décision courante et son historique append-only | identifiants opaques, vue contractuelle |
| `ModerationCaseRegistry` | ModerationReports | exécuter les opérations contractuelles du dossier | Commands V1 vers Results V1 |

### Contraintes

- les ports appartiennent à Application ;
- aucun port n'expose mapper, requête SQL, PDO, transaction Laravel ou snapshot
  Infrastructure ;
- `ModerationCaseStore` reste l'autorité d'écriture du dossier ;
- `ModerationDecisionStore` est read-only hors écriture atomique commandée par
  l'owner du dossier ;
- `ModerationQueueStore` ne décide jamais de l'état métier ;
- aucun store ne partage une table avec un autre domaine.

## Ports externes consommés

| Port de besoin | Provider attendu | Responsabilité minimale | Gate 5.3C |
|---|---|---|---|
| `ModerationAuthorizationGateway` | IAM / owner habilitation | décider si l'acteur peut reporter, instruire, décider ou auditer | IAM Moderator Authorization |
| `ModerationTargetReader` | target owner | existence et éligibilité minimale de la cible | Target Read |
| `ModerationTargetGateway` | target owner | demander l'application idempotente d'une action | Command Handoff |
| `ModerationAuditGateway` | AdministrationAudit | enregistrer la trace transverse minimale | Audit Append |

Ces noms expriment les ports de besoin 5.3. Ils ne remplacent pas les contrats
publics des providers externes. 5.3C devra certifier l'adapter licite ou ouvrir
un amendement versionné.

## Méthodes conceptuelles

| Port | Opération conceptuelle | Garantie |
|---|---|---|
| `ModerationCaseStore` | `read(caseId)` | `Found`, `Missing`, `Corrupted`, `DependencyUnavailable` |
| `ModerationCaseStore` | `save(candidate, expectedVersion, intent)` | atomicité état + intent, résultat fermé |
| `ModerationQueueStore` | `read(criteria, cursor)` | pagination déterministe |
| `ModerationQueueStore` | `claim(itemId, lease)` | lease bornée et concurrence déterministe |
| `ModerationDecisionStore` | `readCurrent(caseId)` | vue propriétaire sans snapshot |
| `ModerationCaseRegistry` | `handle(commandV1)` | orchestration propriétaire future |
| `ModerationTargetGateway` | `request(actionV1)` | aucune mutation directe cross-domain |
| `ModerationAuditGateway` | `append(recordV1)` | idempotence, payload minimal |

## Direction des dépendances

```text
Domain ← Application contracts ← Infrastructure adapters
```

Sont interdits :

- Application vers Infrastructure ;
- ModerationReports vers un Aggregate, Repository ou use case concret externe ;
- SQL ou FK cross-domain ;
- transaction ACID avec le target owner ;
- accès à un Provider, une route ou un Controller externe ;
- consommation d'un événement non certifié comme source autoritaire.

## Ownership transactionnel

Une future transaction locale pourra grouper uniquement :

- état `ModerationCase` ;
- historique propriétaire ;
- journal d'intents propriétaire ;
- message Outbox propriétaire.

La queue, l'audit transverse et l'application d'une action cible convergent par
contrats idempotents, sans transaction distribuée.
