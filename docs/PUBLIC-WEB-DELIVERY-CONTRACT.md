# Public Web Delivery Contract

## Rôle

Cette fondation rend `PublicListingReadModel` directement livrable à un futur adaptateur Web, sans créer cet adaptateur. Elle complète le read model avec deux valeurs finales décidées par ContentSeo et définit son port de résolution publique.

## Chaîne de reconstruction

```text
Sources ContentSeo
  -> ListingSeoDecision
     - HtmlRobotsDirective
     - PublicJsonLd ou null
  -> SeoListingProjection
  -> PublicListingReadModel
  -> PublicListingQuery.findByCanonicalPath()
  -> futur adaptateur Web (hors sprint)
```

Les deux builders recopient les valeurs finales. Leurs contrôles empêchent uniquement une composition structurellement contradictoire ; ils ne prennent aucune décision SEO.

## Contrats livrés

### Robots HTML

`PublicListingReadModel::htmlRobotsDirective` contient directement :

- `index, follow` pour une décision indexable ;
- `noindex, follow` pour une décision non indexable conservée.

Le futur `<meta name="robots">` devra utiliser cette chaîne telle quelle.

### JSON-LD

`PublicListingReadModel::publicJsonLd` contient une chaîne JSON finale, déterministe et validée, ou `null`. Pour une page indexable, elle porte `@context`, `@type`, le canonical, le headline et les faits publics justifiés par ContentSeo. Le futur adaptateur devra l'injecter telle quelle dans un `<script type="application/ld+json">`, sans recomposition.

### Query public

```php
public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel;
```

Le paramètre est le chemin relatif exact du canonical courant, par exemple `annonces/appartement-moderne-dakar`. Le résultat absent est `null`. Le contrat n'accepte pas `ListingId`, ne cherche pas dans les Aggregates et ne définit aucun fallback sur l'historique canonical.

## Préconditions

- le read model public a été reconstruit à partir de projections Search et SEO cohérentes ;
- le canonical courant provient de la décision ContentSeo ;
- toute page indexable possède la directive `index, follow` et un JSON-LD public ;
- toute page non indexable conservée possède `noindex, follow` et aucun JSON-LD public.

## Postconditions

- le consommateur reçoit un `PublicListingReadModel` immutable ou `null` ;
- aucune règle métier, Search ou SEO n'est exécutée lors de la lecture ;
- aucune identité ni donnée publique n'est inventée ;
- une reconstruction identique conserve la directive et le JSON-LD à l'identique.

## Limites

La résolution des anciens canonicals, le statut HTTP, les redirections, le routage, le Controller et la vue appartiennent au futur adaptateur Web. Le faux en mémoire est exclusivement un outil de test du port ; il ne constitue ni une persistance ni une implémentation de production.

## État de reprise du Sprint 3.5

Le Controller, la route et la vue existent désormais et respectent le contrat en isolation. L'application ne dispose toujours pas d'une implémentation autorisée ni d'un binding de production de `PublicListingQuery` : le bootstrap Laravel réel ne peut donc pas servir la page. La directive robots et le JSON-LD sont injectés tels quels dans les tests, sans traduction, fallback ni accès direct aux Aggregates.
