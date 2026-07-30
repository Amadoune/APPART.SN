# Phase 4.9C-R1 — Account Status Historical Coexistence Gate

## 1. Objet

Ce gate fixe la coexistence entre le futur journal `Account Status Lifecycle`
et les fondations historiques :

- l'agrégat `Account`;
- le port `AccountRegistry`;
- le booléen historique `suspended`;
- la version optimiste globale de l'agrégat;
- `AccountSuspended` et `AccountReactivated`.

Il ne crée aucune implémentation.

## 2. Constat audité

`Account` contient aujourd'hui :

- un booléen `suspended`, initialisé à `false`;
- une version globale incrémentée par toutes ses mutations;
- `suspend()` et `reactivate()`;
- les événements historiques correspondants;
- des gardes qui utilisent le statut pour d'autres comportements.

`Account::suspend()` révoque également les rôles actifs. Ce comportement
historique est incompatible avec la décision 4.9A certifiée selon laquelle une
suspension Account Status ne révoque jamais implicitement les rôles.

`AccountRegistry` expose `find`, `add` et `save` avec concurrence optimiste,
mais aucun adaptateur durable de production n'existe actuellement dans le
module. Le port ne définit ni journal lifecycle, ni inspection d'intention.

## 3. Décision d'autorité durable

Pour les opérations ouvertes par la Phase 4.9 :

```text
État Account Status courant
→ futur journal Account Status Lifecycle

Version de concurrence Account Status
→ version dédiée du futur journal

AccountRegistry
→ port historique de l'agrégat Account
→ non autoritaire pour le nouveau lifecycle

Futur journal
→ source d'autorité du lifecycle
→ ni projection ni journal complémentaire
```

Il existe une autorité unique par donnée :

- le journal lifecycle pour le statut et sa version;
- `AccountRegistry` pour les autres données historiques de l'agrégat.

La version globale `Account::version()` et la version lifecycle sont deux
séquences distinctes. Elles ne sont jamais comparées ni synchronisées.

## 4. Interdiction de double décision

Le chemin Phase 4.9 ne peut jamais appeler :

- `Account::suspend()`;
- `Account::reactivate()`;
- `SuspendAccount`;
- `ReactivateAccount`;
- `AccountRegistry::save()` pour appliquer une transition de statut.

Le seul décideur est `AccountStatusWorkflow`. La future Persistance applique
sa transition candidate sans rappeler l'agrégat.

Les chemins historiques ne doivent recevoir aucun binding, endpoint ou
commande du nouveau lifecycle. Leur retrait ou leur neutralisation Runtime
relèvera d'un gate de cutover ultérieur; ce document ne les modifie pas.

## 5. Interdiction de double écriture

Une transition Phase 4.9 écrit exclusivement dans le futur journal lifecycle.
Elle ne met pas à jour le booléen historique `Account::$suspended` et ne
sauvegarde pas l'agrégat.

Ainsi, aucune transaction distribuée ou double écriture
`Account + journal lifecycle` n'existe.

Les futurs faits, contexte, inspection et Outbox devront, lorsqu'ils seront
autorisés, rejoindre la même transaction locale que l'append lifecycle. Ils ne
font pas partie de ce gate.

## 6. Comptes historiques sans contexte lifecycle

L'absence de journal pour un compte existant signifie :

```text
LegacyUninitialized
```

Elle ne signifie jamais `AccountMissing`.

La future Persistance devra appliquer la qualification fermée suivante :

| Compte historique | Entrée lifecycle | Qualification |
|---|---|---|
| absent | absente | `AccountMissing` |
| présent | absente | `LegacyUninitialized` |
| présent | présente et valide | état/version du journal |
| présent | présente mais incohérente | `PersistenceRejected` |
| absent | présente | `PersistenceRejected` |

## 7. Amorçage historique

Pour `LegacyUninitialized`, un snapshot d'amorçage devra être dérivé une seule
fois :

```text
Account::isSuspended() = false → Active
Account::isSuspended() = true  → Suspended
version lifecycle initiale     → 0
```

La version globale historique est conservée uniquement comme provenance
d'amorçage; elle ne devient pas la version lifecycle.

L'amorçage et la première transition devront être atomiques dans la future
Persistance. En concurrence, un seul amorçage peut gagner; les autres lectures
doivent retrouver exactement le même snapshot.

L'amorçage ne republie pas `AccountSuspended` ou `AccountReactivated` et ne
rejoue aucun événement historique.

## 8. Distinction des absences

```text
absence AccountRegistry / source historique
→ AccountMissing

compte présent + absence lifecycle
→ LegacyUninitialized
→ amorçage autorisé

absence d'intention inspectée
→ Inspection::Missing
→ chemin nominal
```

Ces trois résultats ne sont jamais convertibles l'un dans l'autre.

## 9. Lecture après cutover

Après activation de la nouvelle capacité, tout consommateur du statut doit
lire la source lifecycle autoritaire ou ses faits/projections certifiés.

Le booléen historique reste une donnée d'amorçage figée, pas une source de
lecture courante. Aucun composant ne peut arbitrer entre les deux valeurs.

L'activation Runtime devra prouver que les anciens use cases de statut ne sont
plus accessibles avant d'exposer une commande Phase 4.9.

## 10. Événements historiques

`AccountSuspended` et `AccountReactivated` restent des faits historiques de
l'agrégat. Ils ne sont ni supprimés, ni modifiés, ni consommés comme commandes
du nouveau lifecycle.

Les futurs événements Phase 4.9 auront leur propre contrat versionné. Aucune
bijectivité ni compatibilité Event n'est décidée par ce gate.

## 11. Invariants de coexistence

1. une seule source autoritaire pour le statut courant;
2. une seule version de concurrence lifecycle;
3. aucun appel à la mutation historique pour une transition 4.9;
4. aucune double écriture Account/journal;
5. amorçage historique unique et atomique;
6. absence lifecycle distincte de l'absence du compte;
7. aucun rejeu d'événement historique;
8. aucune décision 4.9B déplacée vers la Persistance;
9. aucune lecture du booléen historique après amorçage;
10. aucun effet implicite sur rôles ou sessions.

## 12. Conséquence

Après certification GO de ce gate, le seul sprint autorisable sera :

```text
4.9C — Account Status Persistence Foundation
```

La Persistence Foundation devra respecter ce modèle d'autorité sans modifier
`Account`, `AccountRegistry` ou les décisions 4.9A/4.9B.

## 13. Contraintes

Ce livrable est exclusivement documentaire. Il ne crée aucun Repository,
table, migration, SQL, Runtime, binding, Inspection, Orchestration, Event,
Transport, Routing, Outbox, HTTP ou Worker.
