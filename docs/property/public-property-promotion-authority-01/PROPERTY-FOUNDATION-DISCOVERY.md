# APPART.SN Property Foundation

## Public Property Promotion Authority 01 — Discovery 01

### Nature

Architecture Discovery / Boundary Qualification. Aucun changement applicatif ou de persistance n'appartient à ce chantier.

### Question centrale

Existe-t-il un chemin certifié transformant :

`PropertyAuthoringState → RealEstateCatalog\Property` ?

### Réponse

**MISSING.**

Le dépôt contient deux autorités distinctes :

- `PropertyAuthoringState`, persisté par `PropertyAuthoringStore` dans `real_estate_catalog_authoring.property_authoring` ;
- l'Aggregate `RealEstateCatalog\Domain\Model\Property`, persisté par `PropertyRegistry` dans `real_estate_catalog.properties` et ses adresses.

Aucun use case, contrat, événement, consumer, outbox, adapter ou composition existant ne lit le premier pour créer le second.

### Première frontière manquante exacte

La frontière absente se situe après la stabilisation du Property Authoring et avant toute lecture par `PropertyRegistry` :

`PropertyAuthoringStore::read(propertyId)`

↓ **aucun handoff existant**

`RegisterProperty / PropertyRegistry::add(Property)`

La création Listing, Submit, Review et Approve utilisent des adapters d'éligibilité owner-scoped, mais ne matérialisent jamais l'Aggregate Property.

### Conséquence démontrée

`CertifiedPublicListingProjectionSource` lit exclusivement `PropertyRegistry`. Pour le Listing Published réel `add18bba-6635-4bda-aba9-e66d7bf4084e`, cette lecture retourne `null`, donc `ProjectionSourceAssemblyStatus::PropertyMissing`.

### Discovery, pas Blueprint

Le Discovery établit le manque et les ownerships. Il ne choisit ni le moment, ni le contrat, ni l'événement, ni la transaction d'une future promotion. Ces décisions appartiennent exclusivement à un éventuel **PUBLIC PROPERTY PROMOTION AUTHORITY 01 — BLUEPRINT 01** ouvert séparément.

### Verdict

**GO PROPOSÉ — DISCOVERY 01**

La frontière manquante, les modèles, leurs owners et les dépendances à préserver sont établis sans ambiguïté.
