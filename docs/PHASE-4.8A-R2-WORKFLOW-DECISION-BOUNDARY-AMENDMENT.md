# Phase 4.8A-R2 — Workflow Decision Boundary Amendment

## 1. Objet

Cet amendement lève l'ambiguïté découverte avant l'implémentation de 4.8B. Il
attribue un propriétaire unique à chaque décision du `Place Lifecycle` entre :

- Workflow ;
- Inspection ;
- Orchestration ;
- Persistance.

Il ne modifie pas `PlaceMergeContextV1`, ne crée aucune implémentation et
n'ouvre pas le Workflow Foundation avant certification formelle.

## 2. Principe normatif

Chaque couche décide uniquement à partir des informations dont elle est
propriétaire :

1. **Workflow** : règles métier pures dérivables de l'état courant, de l'action
   et de `PlaceMergeContextV1`.
2. **Inspection** : observation fermée de l'existence et de l'intégrité d'une
   preuve; aucune décision métier.
3. **Orchestration** : séquencement des observations et traduction des issues
   inter-couches en résultat applicatif fermé.
4. **Persistance** : autorité sur la concurrence effective au moment de
   l'écriture; aucune règle de transition métier.

Une couche ne reconstruit jamais une décision appartenant à une autre.

## 3. Matrice officielle des décisions

| Décision / issue | Propriétaire unique | Entrées autorisées | Justification |
|---|---|---|---|
| transition `Enabled → Disabled` | Workflow | état + `Disable` | transition métier pure |
| transition `Disabled → Enabled` | Workflow | état + `Enable` | transition métier pure |
| transition `Enabled → Merged` | Workflow | état + `Merge` + V1 | preuve complète disponible |
| transition `Disabled → Merged` | Workflow | état + `Merge` + V1 | autorisée par gouvernance |
| `AlreadyInState` | Workflow | état + action | entièrement local |
| `TerminalState` | Workflow | `Merged` + action | terminalité certifiée |
| `SameIdentity` | Workflow | source/cible V1 | identités explicites |
| `TargetDisabled` | Workflow | état cible observé V1 | preuve explicite |
| `TargetMerged` | Workflow | état cible observé V1 | preuve explicite |
| `DifferentType` | Workflow | types source/cible V1 | aucune résolution nécessaire |
| `DifferentCountry` | Workflow | pays source/cible V1 | aucune résolution nécessaire |
| `InvalidContext` | Workflow | combinaison structurelle V1 | incohérence métier dérivable sans lecture |
| observation `Found` | Inspection | source + intention | constat d'inspection uniquement |
| observation `Missing` | Inspection | source + intention | absence constatée, pas décision métier |
| observation `Corrupted` | Inspection | source + intention | intégrité constatée, pas décision métier |
| `TargetMissing` | Orchestration | observation `Missing` | traduction applicative, hors Workflow |
| `ReplayConflict` | Orchestration | inspection + contrat de rejeu | comparaison inter-appels |
| `ContextDivergence` | Orchestration | inspection + contexte demandé | classification de rejeu |
| `InspectionCorrupted` | Orchestration | observation `Corrupted` | arrêt fermé avant Workflow |
| `SourceVersionConflict` | Persistance | version attendue + version durable au commit | autorité de concurrence source |
| `TargetVersionConflict` | Persistance | version observée + version durable au commit | stabilité atomique de la cible |

## 4. Ordre de précédence

L'ordre normatif futur est :

```text
Inspection
    → Missing / Corrupted : décision fermée par Orchestration, Workflow non appelé
    → Found : contrôle de rejeu par Orchestration
        → conflit/rejeu : décision fermée, Workflow non appelé
        → nouvelle intention : appel du Workflow pur
            → refus métier : résultat Workflow
            → transition candidate : tentative de Persistance
                → conflit de version : résultat Persistance
                → succès : transition appliquée
```

Cet ordre évite qu'une décision soit produite deux fois. Il ne prescrit aucune
classe, transaction, base de données ou composition Runtime.

## 5. Frontière du Workflow amendée

Le futur Workflow 4.8B possède exclusivement :

- les quatre transitions approuvées ;
- `AlreadyInState` ;
- `TerminalState` ;
- `SameIdentity` ;
- `TargetDisabled` ;
- `TargetMerged` ;
- `DifferentType` ;
- `DifferentCountry` ;
- `InvalidContext`.

Il ne possède plus :

- `TargetMissing` ;
- `SourceVersionConflict` ;
- `TargetVersionConflict` ;
- `ReplayConflict`.

Le retrait signifie un transfert explicite d'ownership, pas une suppression de
ces issues du cycle applicatif complet.

## 6. Frontière de l'Inspection

L'Inspection ne retourne que les observations certifiées :

- `Found` ;
- `Missing` ;
- `Corrupted`.

Elle ne retourne aucune transition, ne traduit aucune observation en décision
métier et ne rappelle jamais le Workflow.

## 7. Frontière de l'Orchestration

L'Orchestration possède :

- la traduction `Missing → TargetMissing` ;
- la traduction d'une corruption en issue fermée ;
- la classification du rejeu et de la divergence ;
- la décision de ne pas appeler le Workflow lorsqu'une issue antérieure est
  terminale pour la requête.

Elle ne redécide aucune règle d'état, de type, de pays ou de terminalité.

## 8. Frontière de la Persistance

La Persistance possède exclusivement :

- `SourceVersionConflict` ;
- `TargetVersionConflict`.

Ces conflits sont déterminés contre l'état durable au point de commit. La
Persistance ne décide ni la validité métier de la cible, ni l'état résultant,
ni le traitement du rejeu.

## 9. Compatibilité avec les contrats certifiés

- `PlaceMergeContextV1` reste inchangé.
- Ses interfaces d'inspection et de rejeu restent inchangées.
- Aucun contrat des Phases 2, 3 ou 4.1 à 4.8A-R1 n'est modifié.
- Les libellés transférés restent disponibles pour les futures couches qui en
  sont propriétaires.

## 10. Conséquence de roadmap

Après certification GO de cet amendement, le seul sprint autorisable sera :

```text
4.8B — Place Lifecycle Workflow Foundation
```

Son périmètre devra appliquer strictement la frontière amendée de la section 5.
Tous les autres jalons resteront fermés.

## 11. Contraintes

Ce livrable est exclusivement documentaire. Aucun Workflow, code applicatif,
Repository, persistance, migration, Runtime, Event, Transport, Routing, Outbox,
HTTP, SQL ou transaction n'est créé.
