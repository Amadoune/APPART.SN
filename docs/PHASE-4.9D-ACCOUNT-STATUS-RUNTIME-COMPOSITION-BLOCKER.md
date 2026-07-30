# Phase 4.9D — Account Status Runtime Composition Blocker

## Statut

```text
4.9C
→ GO CERTIFIÉ
→ FERMÉ

4.9D
→ SUSPENDU AVANT IMPLÉMENTATION
```

Aucun binding, composant Runtime Health ou code de composition 4.9D n'a été
ajouté.

## Constat reproductible

Le constructeur certifié du store exige :

```text
PostgreSqlAccountStatusWorkflowStore
    → PDO
    → AccountRegistry
    → AccountStatusWorkflowMapper
```

`PDO` possède une source Runtime certifiée et le mapper est constructible.
En revanche, l'inventaire du dépôt ne contient :

- aucune classe implémentant `AccountRegistry`;
- aucun binding `AccountRegistry::class`;
- aucune factory Runtime de ce port;
- aucune source durable historique Account certifiée pour le container.

Le port existe uniquement comme contrat Application. Les tests PostgreSQL
4.9C fournissent volontairement un double local, qui n'est ni une
implémentation de production ni un candidat au Runtime.

## Conséquence

Les bindings suivants seraient trompeurs :

```text
AccountStatusWorkflow
→ résoluble

AccountStatusWorkflowMapper
→ résoluble

PostgreSqlAccountStatusWorkflowStore
→ non résoluble faute de AccountRegistry

AccountStatusWorkflowStore
→ alias vers un graphe non résoluble
```

Runtime Health ne peut pas déclarer le store Healthy si sa dépendance
obligatoire ne possède aucune source certifiée.

## Alternatives interdites

4.9D ne peut pas :

- créer un Repository Account;
- introduire une table historique Account;
- fabriquer un `AccountRegistry` vide ou en mémoire;
- utiliser le booléen lifecycle comme substitut de l'existence Account;
- supprimer `AccountRegistry` du constructeur du store;
- rendre la dépendance nullable;
- déclarer un binding qui échoue lors de la première résolution;
- réutiliser `Account::suspend()` ou `Account::reactivate()`.

Ces options déplaceraient les décisions 4.9C-R1, modifieraient une fondation
certifiée ou créeraient une nouvelle persistance hors mandat.

## Blocage des critères 4.9D

| Critère | État |
|---|---|
| Workflow singleton paresseux | techniquement possible |
| mapper singleton paresseux | techniquement possible |
| store singleton paresseux | impossible à résoudre |
| alias port/implémentation unique | déclarable mais non fonctionnel |
| source PostgreSQL certifiée | disponible pour le journal lifecycle |
| source historique Account certifiée | absente |
| Runtime Health sans mutation | impossible à certifier pour le store |
| graphe déterministe complet | non |

Une composition partielle est interdite : Workflow et mapper ne doivent pas
être publiés seuls pendant que le port durable reste non résoluble.

## Amendement préalable autorisé

```text
4.9C-R2 — Account Registry Runtime Source Amendment
```

Cet amendement répond exclusivement à :

```text
Quelle source Runtime certifiée fournit AccountRegistry
pour qualifier l'existence et l'amorçage historique,
sans modifier Account, AccountRegistry ou le journal 041 ?
```

Il audite :

1. l'existence éventuelle d'une source durable Account externe au module;
2. la possibilité d'un adaptateur historique borné;
3. son owner, sa transaction et sa politique de concurrence;
4. la compatibilité avec le modèle d'autorité 4.9C-R1;
5. la stratégie de résolution paresseuse dans le container.

L'audit 4.9C-R2 ne trouve aucune source de production et soumet un verdict
NO GO. Toute reprise de 4.9D reste interdite.

L'autorité certifie ensuite ce NO GO, retient l'option B et ouvre 4.9C-R3.
Cet amendement définit `HistoricalAccountLookupV1`, mais confirme qu'aucune
source durable réelle ne peut l'alimenter. Le journal 041 ne peut pas attester
indépendamment l'existence Account lors de son propre amorçage.

```text
4.9C-R3 → NO GO CERTIFIÉ
4.9P-A → OUVERT
4.9D → RESTE SUSPENDU
```

La fondation complète `AccountRegistry` est désormais la trajectoire mandatée.
Sa certification finale puis une recertification ciblée de 4.9C restent
nécessaires.

## Contraintes maintenues

- aucun Runtime;
- aucun binding Laravel;
- aucune nouvelle persistance;
- aucune migration;
- aucune Inspection ou Orchestration;
- aucun Event, Transport, Routing, Outbox ou HTTP;
- aucune modification de `Account`, `AccountRegistry`, du Workflow ou du
  store certifiés.
