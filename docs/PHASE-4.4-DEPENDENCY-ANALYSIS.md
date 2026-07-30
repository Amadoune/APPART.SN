# Phase 4.4 — Lead Lifecycle Dependency Analysis

## Propriété et dépendances métier

`ContactsLeads` possède Lead, son historique, ses preuves de consentement, sa déduplication et ses décisions de livraison/rejet/clôture.

| Dépendance | Direction | Donnée autorisée | Interdiction |
|---|---|---|---|
| Listing Publication | ContactsLeads → port `ListingCatalog` | preuve de contactabilité | lecture Aggregate/Repository Listing |
| Advertiser/Professional | ContactsLeads → port `AdvertiserCatalog` | preuve d'éligibilité et révision | lecture Aggregate/Repository Professional |
| Property Lifecycle | aucune dépendance directe | aucune | reconstruire l'éligibilité Property |
| Reservation Lifecycle | aucune | aucune | convertir un Lead en Reservation implicitement |

Les preuves sont acquises avant `Lead::create` et conservées dans `LeadEligibilityProof`. Les transitions après création ne relisent pas les modules externes.

## Dépendances techniques constatées

- aucun schéma `contacts_leads` ;
- aucun repository PostgreSQL `LeadRegistry` ;
- aucun binding Laravel ;
- aucune capacité Runtime Health ;
- aucun owner générique Outbox `ContactsLeads` ;
- aucun type Delivery ni Consumer ;
- aucune Inbox de routage ;
- aucune transaction Lead + Outbox.

## Audit préventif contractuel

| Question | Décision blueprint |
|---|---|
| Retour du workflow | résultat fermé `Allowed` / `Denied`, jamais `void` |
| Retour du store | résultats fermés incluant Applied, AlreadyApplied, conflits et corruption |
| Retour du routeur | `LeadLifecycleRoutingResult`, jamais `void` |
| Routage → consommation | matrice fermée certifiée avant Consumer |
| Owner journal | nouveau schéma propriétaire `contacts_leads` via migration additive |
| Owner Outbox | `ContactsLeads → contacts_leads`, migration additive ; migration 005 gelée |
| Composition Laravel | workflow, store, sources, routeur, Inbox puis Consumer, tous paresseux |
| Runtime Health | ajout explicite du workflow/store puis du routeur/Inbox ; aucun appel métier |
| Catalogue Delivery | types issus du catalogue événementiel fermé, version 1 |
| Atomicité | `PostgreSqlAggregateOutboxTransaction` étendu par un port Lead dédié |

## Sources d'éligibilité

Une fondation intermédiaire **4.4C-S1** est obligatoire avant l'orchestration : elle composera des adaptateurs de lecture certifiés pour `ListingCatalog` et `AdvertiserCatalog`, sans lecture Web et sans dépendance Aggregate. Si aucune source durable certifiée ne satisfait ces ports, des snapshots propriétaires additifs devront être cadrés dans ce sprint, avant toute création de Lead.
