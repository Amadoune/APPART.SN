# Content SEO Decision — Sprint 3.3A

## Responsabilité

`ListingSeoDecisionPolicy` est l'unique autorité de composition de la décision SEO d'une annonce. Elle ne publie pas de page et ne modifie aucun Aggregate. Elle transforme des sources locales explicites en `ListingSeoDecision`, modèle métier immutable destiné à alimenter ultérieurement `SeoListingProjection`.

## Entrées

- `ListingSeoSource` : état, contenu public décidé, canonical path, révision, dates et directive d'expiration;
- `SearchSeoSource` : visibilité Search acquise;
- `PropertySeoSource` : disponibilité et type public;
- `PublicGeographySeoSource` : localité publique et ancêtres breadcrumb déjà validés;
- `PublicMediaSeoSource` : URL HTTPS de la ressource média publique;
- historique canonical existant;
- date explicite de décision.

Les ports applicatifs sont `ListingCatalog`, `SearchCatalog`, `PropertyCatalog`, `PublicGeographyCatalog` et `PublicMediaCatalog`. `ListingSeoDecisionSources` orchestre leurs lectures sans importer d'Aggregate externe.

## Sortie

`ListingSeoDecision` contient canonical, historique, contenu, indexabilité, robots, traitement de page, breadcrumb, structured data, média public, traitement d'expiration et dates. Sa factory interdit toute combinaison incohérente. `SeoPageTreatment::Retain|Remove` distingue l'existence de la page de son indexabilité.

## Règles

### Indexabilité et robots

Une décision est `Indexable` uniquement si :

- Listing SEO `Published`;
- Search `Public`;
- Property `Available`;
- headline et description respectent leurs bornes;
- géographie publique présente;
- média public présent;
- dates de publication et expiration présentes et cohérentes.

Toute autre combinaison est `NotIndexable` avec `NoIndexFollow`. Une décision indexable impose `IndexFollow`, breadcrumb, structured data et média.

### Canonical

- le canonical path vient exclusivement de `ListingSeoSource`;
- `CanonicalPolicy` le normalise sous `https://appart.sn`;
- un historique vide est initialisé avec une entrée `Current`;
- un path inchangé conserve l'historique à l'identique;
- un changement réserve toutes les anciennes canonicals pour redirection directe vers la nouvelle;
- une ancienne canonical n'est jamais réutilisée;
- aucun fallback basé sur ListingId n'existe;
- l'unicité globale reste contrôlée par le `SeoProjectionRegistry` existant lors d'une acquisition persistée future.

### Listing expiré

Un Listing `Expired` est toujours non indexable et `noindex,follow`. La page est supprimée par défaut (`Remove`). Elle ne peut être conservée temporairement que si la source fournit explicitement `RetainNoIndex`. Cette conservation n'accorde jamais d'indexabilité.

### Breadcrumb et données structurées

La géographie fournit des niveaux dont labels et URL sont déjà publics. ContentSeo ajoute seulement la page courante avec son headline et sa canonical. Sans géographie publique, aucun breadcrumb ni structured data n'est fabriqué et la décision est non indexable.

Les structured data reprennent uniquement headline, canonical, type Property et localité publique. La ressource média reste un champ explicite de la décision en attendant le modèle public final.

## Exclusions

- aucune `SeoListingProjection` et aucun builder public;
- aucune persistance ou nouveau Repository;
- aucun SQL, migration, API, Controller ou Vue;
- aucun Dispatcher, Outbox ou Unit of Work;
- aucune dérivation depuis des identifiants techniques;
- aucune modification des repositories PostgreSQL certifiés;
- aucun événement tant que la décision pure n'est pas acquise par un Aggregate.

## Relation avec la future projection

La future `SeoListingProjection` devra copier les champs de `ListingSeoDecision`; elle n'aura pas à choisir canonical, robots, breadcrumb, contenu, média ou traitement d'expiration. Elle pourra retourner `null` pour `ExpiredListingTreatment::Remove` et représenter une page non indexée pour `RetainNoIndex`, sans déplacer la décision hors de ContentSeo.
