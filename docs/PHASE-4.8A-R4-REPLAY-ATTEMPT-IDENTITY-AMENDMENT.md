# Phase 4.8A-R4 — Replay Attempt Identity Amendment

## 1. Objet

Cet amendement définit l'identité complète d'une tentative et l'autorité
capable de confirmer un rejeu exact sans modifier les contrats certifiés.

## 2. Identité normative

Une tentative est identifiée par le tuple :

```text
sourceId
+ intentId
+ action
+ PlaceMergeContextV1
+ resultingSourceVersion
```

Deux tentatives ne sont identiques que si tous les éléments sont identiques.
Une action différente interdit donc toujours `AlreadyApplied`, même si la
source, l'intention et le contexte sont identiques.

## 3. Empreinte canonique

L'égalité exacte est représentée par l'empreinte persistante déjà certifiée :

```text
entry_checksum(
    sourceId,
    resultingSourceVersion,
    transition.from,
    action,
    transition.to,
    PlaceMergeContextV1 complet
)
```

Cette empreinte inclut déjà l'action. Aucune colonne, migration ou modification
du journal 038 n'est nécessaire.

## 4. Autorité de classification exacte

La **Persistance** est l'unique autorité capable de confirmer
`AlreadyApplied`, car elle possède simultanément :

- l'action historique persistée;
- l'action demandée portée par la transition candidate;
- le contexte historique;
- le contexte demandé;
- la version résultante;
- l'empreinte canonique.

L'Orchestration ne produit jamais `AlreadyApplied` sur la seule base de
`Inspection::Found`.

## 5. Portée de Replay Inspection

Replay Inspection continue de produire :

- `Found`;
- `Missing`;
- `Corrupted`.

`Found` signifie uniquement :

```text
une tentative antérieure existe pour sourceId + intentId
```

Il ne signifie ni égalité d'action, ni égalité complète, ni
`AlreadyApplied`.

Le contexte inspecté permet encore de détecter une divergence de preuve, mais
ne permet jamais de confirmer seul l'identité complète.

## 6. Portée du classificateur V1 gelé

`PlaceMergeReplayClassifier` reste inchangé. Son résultat
`AlreadyApplied` est désormais interprété comme :

```text
ReplayCandidate
→ contexte et version compatibles
→ confirmation exacte obligatoire par Persistance
```

Il ne constitue plus une décision finale du cycle applicatif. Les résultats
`ContextDivergence`, `Conflict`, `InspectionMissing` et
`InspectionCorrupted` conservent leur caractère fermé.

Cette précision sémantique ne modifie ni la signature ni les types du contrat.

## 7. Matrice corrigée du rejeu

| Inspection | Classification V1 | Persistance | Résultat final |
|---|---|---|---|
| `Missing` | non appelée | appelée après Workflow | résultat Workflow/Persistance |
| `Corrupted` | non appelée | non appelée | `InspectionCorrupted` |
| `Found` | `ContextDivergence` | non appelée | `ContextDivergence` |
| `Found` | `Conflict` | non appelée | `ReplayConflict` |
| `Found` | `AlreadyApplied` candidat | checksum exact | `AlreadyApplied` |
| `Found` | `AlreadyApplied` candidat | checksum différent/version occupée | `ReplayConflict` |

Pour les deux dernières lignes, le Workflow produit d'abord la transition
candidate à partir des entrées certifiées; il ne redécide pas le rejeu.

## 8. Ordre de précédence

```text
Replay Inspection
    → Missing : Workflow puis Persistance
    → Corrupted : InspectionCorrupted
    → Found :
        Replay Classifier
            → divergence/conflit : arrêt
            → candidat compatible :
                Workflow
                → refus métier : arrêt
                → transition candidate :
                    Persistance
                        → AlreadyApplied : rejeu exact confirmé
                        → SourceVersionConflict sur version occupée :
                            ReplayConflict
                        → autre résultat : résultat propriétaire
```

## 9. Responsabilités

| Couche | Responsabilité |
|---|---|
| appelant | fournit action, état courant et V1 qualifié |
| Replay Inspection | constate l'existence d'un historique source/intention |
| Replay Classifier | détecte compatibilité ou divergence de contexte, sans confirmer l'action |
| Workflow | produit une transition candidate ou un refus métier |
| Persistance | compare l'identité complète et confirme `AlreadyApplied` |
| Orchestration | séquence et traduit un conflit d'identité en `ReplayConflict` |

## 10. Compatibilité

- `PlaceMergeContextV1` reste inchangé.
- `PlaceMergeContextInspection` reste inchangé.
- `PlaceMergeContextInspector` reste inchangé.
- `PlaceMergeReplayClassifier` reste inchangé.
- le Workflow, la Persistance, la migration 038 et les bindings restent
  inchangés.

La Persistance 4.8C calcule déjà son checksum avec l'action et retourne
`AlreadyApplied` uniquement pour une empreinte exacte.

## 11. Conséquence pour 4.8E

Après certification GO de R4, 4.8E peut reprendre sans nouveau port ni
infrastructure. Il doit traiter `AlreadyApplied` du classificateur comme un
candidat nécessitant la confirmation du store.

## 12. Contraintes

Cet amendement est exclusivement documentaire. Aucun Orchestrateur, Workflow,
Persistance, Runtime, migration, Event, Transport, Routing, Outbox ou HTTP
n'est créé ou modifié.
