# Phase 4.8A-R3 — Replay Inspection and Target Evidence Boundary Amendment

## 1. Objet

Cet amendement sépare définitivement :

- l'inspection d'une tentative antérieure;
- la qualification de l'existence d'une cible;
- la décision métier de fusion.

Il corrige l'attribution impossible
`Inspection::Missing → TargetMissing` sans modifier
`PlaceMergeContextV1`, les ports certifiés ni une implémentation existante.

## 2. Sémantique certifiable de l'inspection de rejeu

Le contrat :

```text
inspect(sourceId, intentId)
```

observe exclusivement l'historique associé à ce couple.

| Observation | Sémantique unique | Suite normative |
|---|---|---|
| `Found` | tentative antérieure exacte disponible | classification du rejeu |
| `Missing` | aucune tentative antérieure pour cette source et cette intention | poursuite du chemin nominal |
| `Corrupted` | historique présent mais inutilisable | arrêt fermé par Orchestration |

`Missing` ne décrit jamais l'existence de la cible et ne produit jamais
`TargetMissing`.

## 3. Propriétaire de `TargetMissing`

Le propriétaire unique de `TargetMissing` devient la frontière
**Target Evidence Qualification**.

Cette frontière est située avant la construction de `PlaceMergeContextV1`.
Elle seule reçoit :

- `targetId`;
- l'autorité de lecture permettant d'établir l'existence actuelle de la cible;
- les données observées nécessaires à la preuve V1.

Elle produit exclusivement l'une des deux issues conceptuelles :

```text
TargetMissing

ou

QualifiedTargetEvidence
→ construction de PlaceMergeContextV1 en amont de 4.8E
```

Le présent amendement ne crée ni port, ni classe, ni adapter. Il attribue
uniquement l'ownership normatif. La forme contractuelle et l'implémentation
éventuelle de cette qualification exigent un jalon explicitement autorisé si
elles ne sont pas déjà fournies par l'appelant.

## 4. Entrée de 4.8E

La présence d'un `PlaceMergeContextV1` à l'entrée de 4.8E constitue la preuve
que la cible a déjà été qualifiée. Par conséquent :

- 4.8E ne produit pas `TargetMissing`;
- 4.8E ne relit pas la cible;
- 4.8E ne reconstruit pas la preuve;
- 4.8E ne rappelle pas la frontière de qualification.

`TargetMissing` appartient au chemin applicatif antérieur à 4.8E.

## 5. Ordre de précédence corrigé

```text
Target Evidence Qualification
    → TargetMissing : arrêt avant construction de V1
    → Qualified : PlaceMergeContextV1 construit
        ↓
Replay Inspection par sourceId + intentId
    → Corrupted : InspectionCorrupted par Orchestration
    → Found : classification du rejeu
        → AlreadyApplied / ReplayConflict / ContextDivergence : arrêt
    → Missing : nouvelle intention, poursuite
        ↓
Workflow pur
    → refus métier : arrêt
    → transition candidate
        ↓
Persistence
    → SourceVersionConflict / TargetVersionConflict / autre résultat fermé
```

Une issue ferme le traitement et les couches suivantes ne sont pas appelées.

## 6. Matrice corrigée des responsabilités

| Issue | Propriétaire unique | Preuve détenue |
|---|---|---|
| `TargetMissing` | Target Evidence Qualification | lecture d'existence par `targetId` |
| `Found` | Replay Inspection | historique source/intention |
| `Missing` | Replay Inspection | absence d'historique source/intention |
| `Corrupted` | Replay Inspection | historique inutilisable |
| `AlreadyApplied` | Orchestration / classification de rejeu | contexte demandé et inspection exacte |
| `ReplayConflict` | Orchestration | comparaison de rejeu |
| `ContextDivergence` | Orchestration | comparaison des contextes |
| `InspectionCorrupted` | Orchestration | observation `Corrupted` |
| refus métier certifiés | Workflow | état, action et V1 |
| `SourceVersionConflict` | Persistance | version durable source au commit |
| `TargetVersionConflict` | Persistance | version durable cible au commit |

## 7. Correction de 4.8A-R2

La disposition R2
`Inspection::Missing → TargetMissing` est explicitement remplacée par :

```text
Inspection::Missing
→ aucune tentative antérieure
→ poursuite du chemin nominal
```

Toutes les autres attributions de R2 restent inchangées.

## 8. Compatibilité

- `PlaceMergeContextV1` reste intégralement inchangé.
- `PlaceMergeContextInspector` reste intégralement inchangé.
- `PlaceMergeReplayClassifier` reste intégralement inchangé.
- le Workflow 4.8B reste inchangé.
- la Persistance 4.8C reste inchangée.
- les bindings et Runtime Health 4.8D restent inchangés.

## 9. Conséquence pour 4.8E

Après certification GO de R3, 4.8E peut reprendre uniquement si son entrée est
un contexte V1 déjà qualifié. Son périmètre commence à l'inspection de rejeu et
se termine au résultat de Persistance. Il n'inclut pas la qualification de
cible.

## 10. Contraintes

Cet amendement est exclusivement documentaire. Aucun Orchestrateur, Workflow,
Runtime, persistance, migration, Event, Transport, Routing, Outbox ou HTTP
n'est créé ou modifié.
