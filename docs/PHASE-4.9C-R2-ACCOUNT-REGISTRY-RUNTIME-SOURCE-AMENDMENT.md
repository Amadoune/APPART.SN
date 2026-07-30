# Phase 4.9C-R2 — Account Registry Runtime Source Amendment

## 1. Question normative

```text
Quelle source Runtime certifiée fournit AccountRegistry
pour qualifier l'existence et l'amorçage historique,
sans modifier Account, AccountRegistry, le journal 041
ou les décisions déjà certifiées ?
```

## 2. Réponse

```text
AUCUNE SOURCE DE PRODUCTION N'EST PRÉSENTE DANS LE DÉPÔT
```

L'amendement ne peut donc pas désigner une source réelle, un owner technique,
une transaction ou un binding certifiable.

## 3. Inventaire audité

### Module IdentityAccess

Présents :

- agrégat `Account`;
- port `AccountRegistry`;
- use cases historiques;
- doubles de tests;
- journal Account Status 041.

Absents :

- implémentation Infrastructure de `AccountRegistry`;
- mapper de l'agrégat historique;
- table Account historique;
- migration Account historique;
- factory de reconstitution durable;
- binding du port.

### Runtime Laravel

`config/auth.php` définit :

```text
guards    → []
providers → []
passwords → []
```

Il n'existe aucun modèle `User`, aucune classe `Authenticatable` et aucun
provider d'identité exploitable.

### Schémas PostgreSQL

Les migrations 001 à 040 concernent les capacités antérieures. La migration
041 crée exclusivement :

```text
identity_access.account_status_lifecycle_transitions
```

Cette table ne contient ni email, téléphone, nom, Credential, Verification,
rôles ou consentements nécessaires pour reconstituer `Account`. Elle ne peut
donc pas implémenter `AccountRegistry` ni qualifier l'existence historique
indépendamment de sa propre entrée lifecycle.

### Tests

Les seules classes qui implémentent `AccountRegistry` sont des doubles en
mémoire sous `tests/`. Elles ne possèdent ni durabilité, ni concurrence
interprocessus, ni transaction de production, ni owner Runtime.

## 4. Réponses obligatoires

| Question | Réponse auditée |
|---|---|
| source durable Account existante | non |
| composant propriétaire | aucun |
| implémentation du port sans changement | impossible avec l'existant |
| lecture déterministe existence/statut legacy | disponible seulement dans les doubles de test |
| transaction partagée avec le journal | inexistante |
| prévention de `save` depuis 4.9 | le store 4.9C n'appelle pas `save`, mais aucune source n'est injectable |
| autorité unique du journal 041 | préservée, mais bootstrap Runtime impossible |
| binding unique et paresseux | impossible tant que la source manque |
| Runtime Health sans mutation | impossible pour un graphe non résoluble |

## 5. Analyse du port

`AccountRegistry` est un port large :

```text
find
add
save
```

Le store lifecycle utilise seulement `find`, mais 4.9C-R2 interdit de modifier
son constructeur ou le port. Un adaptateur qui implémenterait artificiellement
`add` et `save` par des exceptions ne constituerait pas une implémentation
conforme du contrat historique.

Une source réellement compatible devrait pouvoir reconstituer l'agrégat
complet et garantir les identités uniques ainsi que la concurrence optimiste.
Aucune structure durable disponible ne transporte ces données.

## 6. Transaction et concurrence

En l'absence de source :

- aucune transaction Account ne peut être comparée à celle du journal 041;
- aucun verrou d'existence historique n'est défini;
- aucune politique de concurrence `AccountRegistry::save` n'est matérialisée;
- aucune garantie de cohérence entre lecture legacy et bootstrap ne peut être
  certifiée au Runtime.

Le verrou advisory du store lifecycle protège le journal 041, pas une source
historique inexistante.

## 7. Runtime et Health

Les bindings Workflow et mapper seraient résolubles. Le store resterait non
résoluble sur `AccountRegistry`.

Runtime Health ne doit jamais interpréter :

```text
container->bound(AccountStatusWorkflowStore::class)
```

comme preuve de santé si la première résolution échoue. Aucun binding 4.9D
n'est donc autorisé.

## 8. Verdict de l'amendement

Les critères suivants ne sont pas satisfaits :

- source réelle identifiée;
- owner explicite;
- transaction et concurrence documentables;
- implémentation complète du port;
- résolution Runtime possible;
- sonde Health honnête.

```text
4.9C-R2
→ NO GO PROPOSÉ

4.9D
→ RESTE SUSPENDU
```

## 9. Voies de résolution soumises à gouvernance

Deux options seulement sont recevables pour un futur amendement :

### Option A — Historical Account Persistence Foundation

Créer une source durable propriétaire capable d'implémenter intégralement
`AccountRegistry`, avec schéma, mapping de l'agrégat, unicités, concurrence,
transaction, migration et tests. Cette option constitue une nouvelle
fondation et exige un mandat explicite.

### Option B — Historical Account Lookup V1

Introduire un port de lecture borné à l'existence et au snapshot d'amorçage,
puis amender de façon versionnée la dépendance du store 4.9C. Cette option
modifie un contrat certifié et exige analyse d'impact et recertification de
4.9C.

La table 041 ne peut pas être utilisée comme substitut dans l'une ou l'autre
option.

## 10. Contraintes respectées

L'amendement est exclusivement documentaire. Il ne crée ni source, table,
migration, Repository, binding, Runtime Health, Inspection, Orchestration,
Event, Outbox ou HTTP et ne modifie aucun contrat certifié.
