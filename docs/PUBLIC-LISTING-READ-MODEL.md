# Public Listing Read Model — Sprint 3.4

## Rôle

`PublicListingReadModel` est la première fiche publique complète et passive d'une annonce. Elle est destinée à être consommée ultérieurement par une page web ou une API, sans qu'aucune interface ne soit créée dans ce sprint.

## Sources uniques

Le builder accepte uniquement `SearchListingProjection` et `SeoListingProjection`. Il ne connaît aucun Aggregate, décision SEO source, Catalog, Repository ou framework.

Search fournit identités, caractéristiques, état, média principal et dates. SEO fournit contenu, média public, canonical, indexabilité, robots, breadcrumb, structured data et date de décision.

## Composition

Le builder :

1. retourne `null` si l'une des projections manque;
2. vérifie ListingId et dates communes;
3. vérifie les invariants structurels déjà garantis en amont;
4. copie tous les champs;
5. lève `InconsistentPublicListingProjection` plutôt que corriger une contradiction.

Il ne choisit ni publication, canonical, robots, indexabilité, breadcrumb, structured data ou média.

## États publics

- **Indexable** : Search présente et SEO `indexable/index_follow`;
- **Noindex conservé** : Search présente et SEO `not_indexable/noindex_follow`;
- **Supprimé** : Search ou SEO absente, donc aucun Read Model;
- **Incohérent** : exception structurelle locale.

## Reconstruction

La reconstruction est une copie déterministe. Les objets sources ne sont pas mutés. Aucun timestamp, identifiant, URL ou fallback n'est généré. Supprimer puis reconstruire depuis les mêmes projections produit un objet égal.

## Exclusions et limites

- aucune persistance, Repository, Registry, SQL ou migration;
- aucun Controller, Route, Vue, API ou Resource Laravel;
- aucune règle Search, SEO ou métier;
- aucun prix, téléphone, adresse précise, ville ou quartier inventé;
- aucune vérification possible entre MediaId et URL média publique;
- aucune vérification sémantique entre GeographicPlaceId et breadcrumb.

Les futures interfaces devront consommer ce modèle sans retourner aux Aggregates pour recomposer une décision déjà acquise.
