# Publication Pipeline Analysis — Sprint 3.1

## Décision

La publication est une décision du `Listing`. `PublishListing` compose des lectures synchrones de Property et MediaCollection, puis demande au Listing d'appliquer sa transition. Aucun Aggregate global « annonce complète » n'est créé et `AdministrativeAction` n'intervient pas : le Domain actuel ne rend aucune approbation administrative obligatoire pour `FavorableReview`, `RegularizationValidated` ou `DirectRenewalApproved`.

## Audit des Aggregates

### Property

- Responsabilité : identité et caractéristiques stables du bien, adresse et archivage.
- Invariants utilisés : statut unique `active|archived`; un bien archivé n'est plus mutable; version et chronologie monotones.
- Événements publics existants : `PropertyRegistered`, `PropertyUpdated`, `SurfaceChanged`, `AddressChanged`, `PropertyArchived`.
- Dépendances autorisées : politiques et Value Objects de RealEstateCatalog uniquement. Pour la publication, ListingLifecycle ne reçoit que `PropertyAvailability` par son `PropertyCatalog` en lecture seule.

### MediaCollection

- Responsabilité : galerie d'un Property, propriété des médias, statut, ordre et média principal.
- Invariants utilisés : chaque item appartient à la collection; identités/checksums/ordres actifs sont uniques; au plus un média actif principal; un média terminal n'est jamais principal; retirer ou archiver le principal exige un remplacement actif.
- Événements publics existants : `MediaAdded`, `MediaMarkedPrimary`, `MediaRemoved`, `MediaArchived`, `MediaReordered`, `MediaCaptionChanged`.
- Dépendances autorisées : identité Property vérifiée à la création. Pour la publication, ListingLifecycle reçoit seulement un état normalisé par `MediaCatalog`; il n'importe ni le modèle Media ni son Registry.
- Conséquence : une collection créée peut être vide. Une collection reconstituée peut légitimement n'avoir aucun média actif; le pipeline doit donc exiger un média actif et un principal actif. Aucun événement Media n'est produit, car la publication ne modifie pas la galerie.

### Listing

- Responsabilité : état officiel et historique des transitions d'une annonce.
- Invariants utilisés : transition présente dans le graphe; trigger et origin autorisés; motif/acteur/date présents; Property `Eligible` pour publier; révision unique; date non rétroactive; expiration future; médias de publication `Eligible`.
- Événements publics existants : `ListingDraftCreated`, `ListingSubmitted`, `ListingSentToReview`, `ListingChangesRequested`, `ListingPublished`, `ListingSuspended`, `ListingExpired`, `ListingWithdrawn`, `ListingRejected`, `ListingArchived`.
- Dépendances autorisées : `ListingTransitionPolicy`, faits externes normalisés `PropertyAvailability` et `PublicationMediaAvailability`; aucune dépendance directe vers un autre module.

### AdministrativeAction

- Responsabilité : action administrative auditée, approbations, décision et règle des quatre yeux.
- Invariants : cycle recorded/approved/rejected, preuves internes, approbateurs distincts lorsque requis, audit append-only.
- Événements : `AdministrativeActionRecorded`, `AdministrativeActionApproved`, `AdministrativeActionRejected`, `AuditEntryRecorded`, `FourEyesSatisfied`.
- Décision Sprint 3.1 : hors pipeline. Aucun invariant actuel de Listing n'exige un AdministrativeActionId et l'ajouter inventerait une règle, une écriture multi-Aggregates et une dépendance non justifiées.

## Préconditions minimales retenues

1. Listing existant (`ListingNotFound` sinon). Son `propertyId` est la référence d'autorité du parcours.
2. Property `Eligible`. `Missing`, `Archived`, `Ineligible` et `Unavailable` sont déjà distingués par le Domain ListingLifecycle et refusés par sa policy.
3. MediaCollection existante et rattachée au même Property.
4. Au moins un média actif.
5. Un média principal actif.
6. Transition Listing autorisée depuis l'état courant, avec trigger/origin conformes et révision unique.
7. Expiration strictement postérieure à la date de décision.

Les états Media sont ordonnés du plus structurel au plus précis : `Missing`, `PropertyMismatch`, `WithoutActiveMedia`, `WithoutPrimaryMedia`, `Eligible`. Aucun contrôle de quantité, type, qualité, modération ou droit média supplémentaire n'est ajouté : ces seuils restent ouverts dans les documents métier.

## Atomicité et effets

Property et MediaCollection sont uniquement lus. Seul le Listing détaché est muté, puis sauvegardé avec sa version attendue. Une précondition refusée intervient avant `save`. Si `save` échoue, le repository certifié ne rend visible ni statut, ni expiration, ni événement. Une Unit of Work globale serait donc inutile et interdite.

## Événements

Une publication réussie produit exactement l'événement existant `ListingPublished`, enregistré par l'Aggregate après validation et avec la version résultante. Aucun événement Property, Media ou AdministrativeAction n'est produit. L'ordre est : validations pures, mutation/révision Listing, création de `ListingPublished`, sauvegarde atomique. La diffusion est hors périmètre (aucun Dispatcher/Outbox).
