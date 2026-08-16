# Current Property Lifecycle

| Étape réelle | Property existant | Owner / durabilité | Événement ou handoff exécuté |
|---|---|---|---|
| Création Property propriétaire | `PropertyAuthoringState` | RealEstateCatalog Authoring ; table Authoring ; owner IAM persistant | Aucun événement Property Domain ou lifecycle. Écriture idempotente par intent. |
| Création Listing | Le même `PropertyAuthoringState`; aucun Aggregate Property | Authoring reste owner. Listing Lifecycle crée son propre Aggregate et référence le même `propertyId` | `PropertyAuthoringCatalogAdapter` fournit uniquement `Eligible/Unavailable`; aucun payload Property transféré. |
| Media | `PropertyAuthoringState` résolu owner-scoped | Property Authoring reste owner | `PropertyAuthoringMediaCatalogAdapter` reconnaît l'identité ; aucune promotion Property. |
| Submit | `PropertyAuthoringState` demeure inchangé ; Aggregate Property toujours absent | Transaction Listing ; Authoring conserve le Property | Submit synchronise Aggregate/Workflow Listing, prépare uniquement `transactionKind` dans `AuthoringPublicFactHandoffV1`, émet `ListingSubmitted`; aucun handoff Property. |
| BeginReview | Property Authoring lu comme disponibilité par adapter | Listing Lifecycle consomme une réponse d'éligibilité, pas les faits Property | `SendToReview` ; aucun Property event. |
| ApproveAndPublish | Property Authoring encore utilisé comme disponibilité ; Aggregate Property absent | Listing Lifecycle scelle Listing/Public Facts ; Property Authoring inchangé | `ListingPublished`/delivery Listing ; aucun `RegisterProperty`, aucun événement Property. |
| Projection activation | `CertifiedPublicListingProjectionSource` exige l'Aggregate `Property` via `PropertyRegistry` | Public Projection est consumer read-only des sources autoritatives | `PropertyRegistry::find` retourne `null` → `PropertyMissing`; aucun read model ni ledger Projection. |

## Cycle Property distinct existant

Le dépôt possède par ailleurs un cycle Aggregate Property autonome :

`RegisterProperty → Aggregate Property → PropertyRegistry → Property Lifecycle → Property lifecycle events/outbox`.

Ce cycle n'est jamais invoqué par le parcours propriétaire P05/P08 observé. La similitude d'identifiant ne constitue pas un handoff.
