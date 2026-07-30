# Phase 4.9C-R3 — Historical Account Source Resolution Amendment

## Mandat

4.9C-R3 est un amendement exclusivement documentaire. Il doit définir le port
minimal `HistoricalAccountLookupV1`, identifier une source durable réelle et
préparer la recertification ciblée de 4.9C. Il ne crée aucun contrat exécutable,
aucune migration, aucun binding et aucun composant Runtime.

## Décision de forme contractuelle

Le port futur appartient au bounded context `IdentityAccess`. Il est strictement
en lecture :

```text
HistoricalAccountLookupV1::lookup(AccountId)
→ HistoricalAccountFound
| HistoricalAccountMissing
| HistoricalAccountCorrupted
```

`HistoricalAccountFound` transporte exactement :

- `accountId`;
- `isSuspended`;
- `historicalVersion`.

Le port ne propose ni `add`, ni `save`, ni commande, ni décision de lifecycle.
Il ne remplace pas `AccountRegistry` et n'en acquiert aucune autorité.

## Sémantique fermée

| Résultat | Signification exclusive |
|---|---|
| `HistoricalAccountFound` | une source Account historique autoritative contient une ligne intègre pour l'identité demandée |
| `HistoricalAccountMissing` | la source autoritative atteste l'absence de cette identité |
| `HistoricalAccountCorrupted` | la source existe mais ne permet pas une lecture intègre et complète des trois champs |

Une panne technique, une source non configurée ou une source non certifiée ne
doit jamais être traduite en `Missing`.

## Propriété des responsabilités

| Responsabilité | Owner unique |
|---|---|
| identité et état historique Account | source historique `IdentityAccess` |
| lecture et classification Found/Missing/Corrupted | adaptateur futur `HistoricalAccountLookupV1` |
| décision Active/Suspended | `AccountStatusWorkflow` |
| journal et version du lifecycle | store 4.9C / journal 041 |
| séquencement futur | Orchestration |
| composition paresseuse | Runtime Composition |
| santé de résolution | Runtime Health |

Le lookup fournit une preuve. Il ne décide jamais une transition et ne modifie
jamais le compte ou le journal lifecycle.

## Audit de la source durable

L'inventaire du dépôt confirme :

- aucune table Account ou User;
- aucune migration historique Account;
- aucune implémentation durable de `AccountRegistry`;
- aucun modèle Auth exploitable;
- aucun provider Auth configuré;
- aucun mapper de source Account;
- aucun binding Runtime de source Account.

La table `identity_access.account_status_lifecycle_transitions` créée par la
migration 041 ne peut pas servir de source historique d'amorçage :

1. elle est précisément la destination à amorcer;
2. elle ne peut attester l'existence indépendante d'un compte;
3. son absence de ligne signifie « lifecycle non amorcé », jamais « compte
   absent »;
4. l'utiliser rendrait la preuve circulaire.

Les autres schémas métier n'appartiennent pas à `IdentityAccess` et ne portent
ni l'état historique Account ni sa version de provenance. Les doubles de tests
sont exclus.

```text
Source durable réelle compatible
→ AUCUNE
```

Le nom, le schéma ou l'owner technique d'une source future ne peuvent pas être
inventés par un amendement documentaire.

## Transaction et concurrence futures

Le modèle peut être défini, mais pas certifié sans source :

- `lookup` rejoint la transaction PostgreSQL propriétaire du store lorsque la
  cohérence bootstrap exige un même snapshot;
- la lecture et l'écriture bootstrap partagent la même connexion et la même
  frontière transactionnelle;
- le verrou consultatif par `accountId` du store demeure l'arbitre des
  amorçages concurrents;
- la source fournit une version stable et monotone;
- aucune lecture hors transaction ne remplace la preuve de bootstrap.

Ces règles sont des préconditions, pas des garanties actuellement disponibles.

## Runtime Composition et Runtime Health futurs

Après existence et certification d'une source :

```text
AccountStatusWorkflowStore
→ PostgreSqlAccountStatusWorkflowStore
→ PDO
→ HistoricalAccountLookupV1
→ adaptateur PostgreSQL certifié
```

Tous les composants seront des singletons paresseux. Le port et l'adaptateur
résoudront vers la même instance. Aucun appel n'aura lieu au bootstrap.

Runtime Health vérifiera au minimum la résolvabilité, l'identité de l'alias, la
présence du schéma, sa compatibilité de version et une sonde structurelle sans
mutation. Il ne peut pas déclarer ce graphe `Healthy` aujourd'hui.

## Impact et recertification ciblée de 4.9C

La recertification versionnée remplacera uniquement la dépendance de lecture
historique du store :

```text
AccountRegistry
→ HistoricalAccountLookupV1
```

Elle adaptera `read`, `bootstrap` et le contrôle d'existence de `append` sans
changer le Workflow, les diagnostics fermés, le schéma 041,
`AccountStatusContextV1` ou les décisions certifiées antérieures.

Elle exigera des tests unitaires, PostgreSQL, concurrence, transaction et
Architecture dédiés. Elle n'est pas exécutée pendant R3.

## Verdict certifié

Le contrat minimal est borné, mais aucune source historique réelle n'est
identifiée. Créer cette source exige un nouveau mandat.

```text
4.9C-R3
→ NO GO CERTIFIÉ
→ FERMÉ

4.9D
→ RESTE SUSPENDU
```

`HistoricalAccountLookupV1` est abandonné comme trajectoire principale.
L'autorité ouvre la piste 4.9P pour une implémentation complète du port
`AccountRegistry`.
