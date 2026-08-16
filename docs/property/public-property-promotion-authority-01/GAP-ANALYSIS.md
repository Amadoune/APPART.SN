# Gap Analysis

## Résultat

**MISSING**

## Chemin recherché

`PropertyAuthoringState → RealEstateCatalog\Property`

## Preuve négative complète

- `PropertyAuthoringStore` n'expose que `read` et `save` de son propre état.
- `RegisterProperty` reçoit tous les paramètres Domain mais n'est appelé par aucune opération Authoring, Submit, Review ou Approve.
- `CreateLocalFirstListing` matérialise directement un Property P02 par `Property::register` et `PropertyRegistry::add`; ce bootstrap local ne lit pas `PropertyAuthoringState`, n'est pas owner-scoped et ne couvre donc pas la frontière recherchée.
- `PropertyRegistry::add` n'est jamais composé avec `PropertyAuthoringStore`.
- les adapters Authoring de Listing et Media ne retournent que disponibilité/ownership ; aucun Aggregate.
- `AuthoringPublicFactHandoffV1` transporte exclusivement `transactionKind` pour le Listing.
- les événements Domain Property ne naissent qu'après création de l'Aggregate.
- les événements Property Lifecycle supposent un Property lifecycle et ne portent aucun fait de création.
- le payload Property de Public Projection Delivery ne contient que `propertyId` et déclenche un fan-out.
- `CertifiedPublicListingProjectionSource` ne possède aucun fallback vers Authoring et échoue fermé avec `PropertyMissing`.

## Première frontière manquante exacte

Il n'existe aucune composition applicative de production entre :

1. la lecture autorisée de l'état durable `PropertyAuthoringState` ;
2. l'invocation autoritative de `RegisterProperty` / `PropertyRegistry::add`.

Le manque est antérieur à Projection. Projection révèle le défaut ; elle n'en est pas l'owner.

## Données non résolues par l'architecture actuelle

Même avec type/ville/quartier présents, l'Aggregate exige des décisions supplémentaires déjà propres à son Domain : référence, rooms, bathrooms, business year, adresse structurée/place utilisable et validations `PropertyTypePolicy`. Le Discovery ne déduit aucune valeur.

## Aucune correction proposée

Le choix du moment, de l'owner applicatif, du contrat, du payload, du replay et de la transaction reste à qualifier dans un Blueprint séparé. Aucun de ces choix n'est implicite dans le présent verdict.
