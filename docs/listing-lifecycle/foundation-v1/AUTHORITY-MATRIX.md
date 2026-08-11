# Authority Matrix

| Valeur / décision | Owner | Source autoritative | Résolution dans la Gateway |
|---|---|---|---|
| ListingId | PublicationReview comme référence, Listing Lifecycle comme cible | intention adressée | conversion typée uniquement |
| commandId | PublicationReview | intention immutable | clé du ledger Lifecycle et intent de révision |
| expectedVersion | Listing Lifecycle | publication workflow | comparaison avant mutation |
| Actor | IAM, transporté par PublicationReview | acteur déjà autorisé | `ActorId`, sans nouvelle décision IAM |
| occurredAt | intention PublicationReview | instant de commande | conversion UTC canonique |
| ListingRevisionId | Listing Lifecycle | `ListingRevisionAllocatorV1` | allocation par opération et `commandId` |
| TransitionEvidence trigger | Listing Lifecycle | catalogue existant | BeginReview → `ReviewStarted`; Approve → `FavorableReview` |
| TransitionEvidence origin | Listing Lifecycle | catalogue existant | `Moderation` pour les deux commandes |
| TransitionEvidence reason | Listing Lifecycle | modèle simplifié certifié | `null`, autorisé pour ces deux transitions |
| ExpirationDate | Listing Lifecycle | `ListingPublicationExpirationPolicyV1` | dérivation depuis `occurredAt` |
| PropertyId | Listing Aggregate | `ListingRegistry` | lecture interne de l'Aggregate |
| Property availability | RealEstateCatalog, consommé par Lifecycle | `PropertyCatalog` | use cases existants uniquement |
| MediaCollectionId | Media, consommé par Lifecycle | `MediaCollectionOwnershipLookup` depuis PropertyId | adapter interne owner-scoped, jamais exposé à PublicationReview |
| Media eligibility | Media, consommé par Lifecycle | `MediaCatalog` | `PublishListing` uniquement |
| Public Facts | Listing Lifecycle | `AuthoringPublicFactHandoffV1` | `PublishListing` candidate/seal |
| Aggregate version | Listing Lifecycle | ListingRegistry snapshot | optimistic locking interne |
| Workflow version/state | Listing Lifecycle | ListingPublicationWorkflowStore | vérification transactionnelle |

## Principe

Les owners externes restent propriétaires de leurs faits. La Gateway ne les copie pas comme décisions : elle les consulte via leurs ports certifiés, à l'intérieur de la composition Listing Lifecycle.
