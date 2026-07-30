# Public Web Delivery Contract — analyse

## Constat de départ

Le Sprint 3.5 a conclu `NO GO` à juste titre. `PublicListingReadModel` exposait la décision interne `RobotsPolicy` (`index_follow` ou `noindex_follow`) et une structure métier générique, mais pas les deux valeurs finales directement consommables par un adaptateur HTML : la directive robots exacte et un document JSON-LD public valide. Aucun port applicatif ne permettait non plus de résoudre une page par son identité publique.

Les projections Search, SEO et le read model public sont reconstruisibles. Aucun nouveau stockage n'est nécessaire pour compléter leurs contrats.

## Frontières de responsabilité

| Élément | Responsable | Nature |
|---|---|---|
| Indexabilité et policy robots | ContentSeo | décision métier existante |
| `index, follow` / `noindex, follow` | ContentSeo | représentation HTML finale de la décision |
| Sélection et validation des faits structurés | ContentSeo | décision métier |
| `@context`, `@type` et JSON déterministe | ContentSeo | document public final |
| Copie des valeurs SEO finales | `SeoListingProjectionBuilder` | projection passive |
| Composition Search + SEO | `PublicListingReadModelBuilder` | composition passive et contrôles structurels |
| Résolution par identité publique | `PublicListingQuery` | port applicatif de lecture |
| Réponse HTTP et rendu | futur adaptateur Web | hors sprint |

## Directive robots HTML

`HtmlRobotsDirective` est un Value Object ContentSeo fermé sur deux valeurs démontrées :

- `RobotsPolicy::IndexFollow` donne exactement `index, follow` ;
- `RobotsPolicy::NoIndexFollow` donne exactement `noindex, follow`.

Une autre chaîne est rejetée. La projection et le read model ne recalculent pas cette valeur.

## JSON-LD public

`PublicJsonLd` est produit depuis `StructuredData`, déjà validé par ContentSeo, et depuis l'éventuelle ressource média publique. Le document conserve uniquement les faits disponibles :

- `@context`: `https://schema.org` ;
- `@type`: `RealEstateListing` ;
- `name` ;
- `url` ;
- `category` ;
- `addressLocality` ;
- `image`, uniquement lorsqu'une ressource média publique existe.

L'ordre des clés est fixe et l'encodage utilise des options explicites : la même décision produit donc la même chaîne JSON. Une décision non indexable ne publie pas de JSON-LD. Aucun prix, coordonnées, image dérivée, slug ou autre fait spéculatif n'est ajouté.

## Identité de lecture publique

Le port `PublicListingQuery::findByCanonicalPath(string)` prend le chemin relatif du canonical courant déjà décidé par ContentSeo, par exemple `annonces/appartement-moderne-dakar`. Cette identité est :

- publique et décidée par ContentSeo ;
- exacte, sans normalisation ou correction implicite ;
- distincte de `ListingId` ;
- limitée au canonical courant.

L'historique canonical reste une information de décision utile à un futur traitement explicite des redirections. Le query courant ne transforme pas une ancienne URL en alias. Il retourne le read model ou `null`.

## Choix d'implémentation

Seule l'interface applicative existe en production. L'implémentation en mémoire réside sous `tests/` afin de certifier le protocole sans introduire de cinquième persistance, de registry ou de repository.

## Exclusions confirmées

Aucun Controller, Route, View, API, Repository, SQL, migration, Dispatcher, Outbox, Unit of Work, Service Provider ou mécanisme de persistance n'est créé. Les Aggregates et les quatre repositories PostgreSQL certifiés ne sont pas modifiés.
