# Phase 4.9A-R1 — Account Status Decision Boundary Amendment

## 1. Objet

Cet amendement attribue un propriétaire unique à chaque décision du futur
`Account Status Lifecycle` entre Workflow, Inspection, Orchestration et
Persistance. Il sépare définitivement le statut des rôles, sessions,
Credentials, Verification et Consent.

Le document décrit un futur contexte versionné sans créer de contrat
technique. Il n'autorise ni Workflow ni infrastructure.

## 2. Principe normatif

1. **Workflow** : décide uniquement la validité métier de la paire état/action
   et la transition candidate.
2. **Inspection** : constate l'historique d'une tentative et en qualifie
   l'identité exacte; elle ne décide aucun statut.
3. **Orchestration** : séquence les couches et traduit leurs issues fermées
   sans recalculer une décision.
4. **Persistance** : possède l'existence durable, la concurrence effective et
   l'append atomique; elle ne décide aucune transition métier.

Une couche ne relit, ne reconstruit et ne remplace jamais une décision dont une
autre couche est propriétaire.

## 3. Matrice normative des décisions

| Décision / observation | Owner unique | Entrées propres | Portée |
|---|---|---|---|
| `Active + Suspend → Suspended` | Workflow | état + action | transition pure |
| `Suspended + Reactivate → Active` | Workflow | état + action | transition pure |
| `AlreadyInState` | Workflow | état + action | refus métier |
| `InvalidContext` | Workflow | cohérence structurelle du contexte V1 | refus avant transition |
| `Found` | Inspection | compte + intention | historique disponible |
| `Missing` | Inspection | compte + intention | nouvelle intention possible |
| `Corrupted` | Inspection | preuve durable illisible/incohérente | observation uniquement |
| `AlreadyApplied` | Inspection | identité complète demandée + tentative trouvée | rejeu strictement identique |
| `ReplayConflict` | Inspection | même compte/intention, identité différente | refus de rejeu |
| `InspectionCorrupted` | Orchestration | observation `Corrupted` | traduction applicative fermée |
| poursuite nominale | Orchestration | `Missing` | appelle le Workflow, aucune décision métier |
| restitution `AlreadyApplied` | Orchestration | classification Inspection | Workflow non appelé |
| restitution `ReplayConflict` | Orchestration | classification Inspection | Workflow non appelé |
| `AccountMissing` | Persistance | identité + état durable | absence de source |
| `VersionConflict` | Persistance | version attendue + version durable au commit | concurrence |
| `PersistenceRejected` | Persistance | impossibilité fermée d'append atomique | refus technique fermé |
| `Applied` | Persistance | transition candidate + append réussi | application durable |

`Missing` ne signifie jamais `AccountMissing`. Il signifie exclusivement
qu'aucune tentative antérieure n'existe pour le compte et l'intention.

## 4. Ordre de précédence

```text
Persistance qualifie l'existence courante
    → AccountMissing : arrêt
    → compte présent : préparation du contexte observé
        → Inspection du rejeu
            → Corrupted : InspectionCorrupted par Orchestration
            → Found : classification
                → AlreadyApplied : restitution, Workflow non appelé
                → ReplayConflict : restitution, Workflow non appelé
            → Missing : poursuite nominale
                → Workflow
                    → AlreadyInState / InvalidContext : arrêt
                    → transition candidate
                        → Persistance au commit
                            → VersionConflict / PersistenceRejected
                            → Applied
```

L'ordre est normatif mais ne prescrit aucune classe, requête, transaction ou
technologie.

## 5. Frontière du Workflow

Le futur Workflow reçoit exclusivement un état courant, une action et le
contexte V1 préparé. Il possède les deux transitions, `AlreadyInState` et
`InvalidContext`.

Il ne possède jamais :

- `AccountMissing`;
- `VersionConflict`;
- `Found`, `Missing` ou `Corrupted`;
- `AlreadyApplied` ou `ReplayConflict`;
- les effets sur rôles, sessions, Credentials, Verification ou Consent.

Il ne lit ni Registry, ni projection, ni Runtime.

## 6. Frontière de l'Inspection

L'Inspection identifie une tentative par :

```text
accountId + intentId + action + contexte versionné
```

Elle observe `Found`, `Missing` ou `Corrupted`. Lorsqu'une tentative est
trouvée, elle classe uniquement `AlreadyApplied` ou `ReplayConflict`.
Une action différente ne peut jamais être `AlreadyApplied`.

Elle ne vérifie pas l'existence courante du compte, ne décide pas le statut et
n'appelle jamais le Workflow.

## 7. Frontière de l'Orchestration

L'Orchestration possède exclusivement le séquencement et la restitution
fermée des résultats des couches propriétaires. Elle transforme une
observation corrompue en `InspectionCorrupted` et décide quels appels ne
doivent pas être effectués après une issue terminale pour la requête.

Elle ne compare pas elle-même les identités de rejeu, ne valide pas la paire
état/action et ne contrôle pas la version durable.

## 8. Frontière de la Persistance

La Persistance possède :

- `AccountMissing`;
- `VersionConflict`;
- `PersistenceRejected`;
- l'append durable et le résultat `Applied`.

Elle fournit l'état et la version observés nécessaires à la préparation du
contexte, puis revalide la version au point de commit. Elle ne transforme
jamais une action en état cible.

## 9. Séparation des sous-domaines

Une transition Account Status publiera ultérieurement un fait seulement après
le jalon Event Contract. Elle ne révoque aucun rôle et n'invalide aucune
session. Role Assignment et Authentication / Session restent seuls owners de
leurs comportements.

Credentials, Verification et Consent ne sont ni lus pour décider le statut,
ni créés, ni modifiés, ni supprimés par le Workflow.

## 10. Résultats fermés pressentis

L'espace complet des issues applicatives futures est :

```text
Applied
AlreadyInState
InvalidContext
AlreadyApplied
ReplayConflict
InspectionCorrupted
AccountMissing
VersionConflict
PersistenceRejected
```

Les observations `Found`, `Missing` et `Corrupted` restent internes à la
frontière Inspection/Orchestration. Aucun `default`, aucune exception métier
ouverte et aucune branche implicite ne sont autorisés.

## 11. Compatibilité et gel

- les six décisions 4.9A restent inchangées;
- `Account` et `AccountRegistry` restent inchangés;
- la Phase 4.8 reste gelée;
- les migrations 038, 039 et 040 restent inchangées;
- aucun contrat des phases certifiées n'est modifié.

## 12. Conséquence de roadmap

Après certification GO de cet amendement, le seul sprint autorisable sera :

```text
4.9B — Account Status Workflow Foundation
```

Tous les autres jalons resteront fermés.

## 13. Contrainte d'amendement

Ce livrable est exclusivement documentaire. Il ne crée aucun Workflow,
contrat technique, Repository, persistance, migration, Runtime, Event,
Transport, Routing, Outbox, HTTP, Worker, SQL ou transaction.
