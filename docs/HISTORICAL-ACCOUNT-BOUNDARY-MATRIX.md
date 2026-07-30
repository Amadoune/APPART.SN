# Historical Account — Boundary Matrix

| Responsabilité | Owner unique | Entrées | Sorties | Interdictions |
|---|---|---|---|---|
| Port de persistance historique | `AccountRegistry` | AccountId ou Account | Account détaché / écriture fermée | Workflow, HTTP, Event, statut lifecycle |
| Extraction et hydratation | `AccountPersistenceMapper` | Account ou Snapshot V1 | Snapshot V1 ou Account reconstitué | SQL, mutation métier, réflexion, événement |
| Secrets de persistance | `SensitivePersistenceValueV1` | Secret non vide | Révélation bornée au slice autorisé | texte implicite, JSON, sérialisation PHP, debug en clair |
| Persistance durable | `PostgreSqlAccountRepository` | Contrat `AccountRegistry` | Racine et enfants atomiques | décision métier, Runtime, Event, Outbox, HTTP |
| Schéma Historical Account | migration 042 | Snapshot V1 | cinq tables `identity_access` | modification du journal 041 |
| Composition | Runtime 4.9P-D | Container Laravel | singleton Repository/Mapper et alias port | résolution, SQL ou transaction au bootstrap |
| Statut courant | journal 041 / future piste Account Status | contexte lifecycle dédié | statut et version lifecycle | utiliser la version historique comme version lifecycle |

## Séparation normative des versions

```text
Historical Account
→ existence de l'Account
→ agrégat historique complet
→ historical_version

Account Status journal 041
→ statut lifecycle courant
→ lifecycle_version
```

Ces versions sont indépendantes. Le chemin Account Status ne doit jamais
appeler `Account::suspend()`, `Account::reactivate()`, `SuspendAccount`,
`ReactivateAccount` ou `AccountRegistry::save()` pour appliquer une transition
lifecycle.

## Frontières de consommation

Historical Account ne publie aucun fait, n'alimente aucune projection et
n'expose aucun secret à HTTP, Event, logs ou Runtime Health. Le Runtime Health
reste gelé à 55 capacités et ne résout pas le graphe Historical Account.
