# Public Page Adapter Analysis — Sprint 3.5

## Verdict

`NO GO`. `PublicListingReadModel` contient suffisamment de contenu pour une fiche publique, mais pas encore une représentation HTML SEO directement injectable sans transformation sémantique. Aucun mécanisme autorisé ne permet non plus à une requête HTTP de recevoir un Read Model.

Créer un Controller et une Blade dans cet état produirait soit une route inexécutable, soit des balises SEO invalides, soit de nouvelles décisions dans l'adaptateur. Ces trois résultats violent le critère de GO.

## Champs directement affichables

- `headline` et `description`, lorsqu'ils sont présents;
- `publicMediaUrl`, comme source du média public déjà décidée;
- `propertyType`, `surfaceSquareMeters` et `roomCount`;
- `breadcrumb`, car labels et URL sont déjà composés;
- éventuellement `publishedAt` et `expiresAt`, bien que le périmètre d'affichage demandé ne les exige pas.

Les valeurs optionnelles peuvent légitimement manquer sur une page noindex conservée. La Vue pourrait uniquement omettre un élément absent; elle ne doit fabriquer aucun remplacement.

## Champs SEO disponibles mais non adaptés au HTML

### Canonical

`canonicalUrl` est directement utilisable dans `<link rel="canonical">`.

### Robots

Le Read Model expose `index_follow` ou `noindex_follow`. Les valeurs attendues par une balise `<meta name="robots">` sont des directives comme `index, follow` ou `noindex, follow`. Remplacer `_` par une virgule dans la Vue, un ViewModel ou le Controller serait une transformation SEO et dupliquerait la signification de `RobotsPolicy`.

### Structured data

Le Read Model expose une structure interne :

```text
type: real_estate_listing
facts: addressLocality, category, name, url
```

Elle ne contient ni `@context` ni `@type` JSON-LD public. Associer `real_estate_listing` à un type schema.org et créer l'enveloppe JSON-LD constituerait une décision ContentSeo absente. Encoder simplement la structure interne dans un script `application/ld+json` produirait un document qui n'exprime pas le vocabulaire public attendu.

## Champs techniques à masquer

- `listingId`, `propertyId`, `mediaCollectionId` et `primaryMediaId`;
- `listingStatus`, déjà reflété par l'existence de la page;
- `geographicPlaceId`, qui n'est pas un libellé public;
- `canonicalHistory`, utile aux redirections futures mais pas à l'affichage;
- `indexability`, `decidedAt` et `expiredListingTreatment`, qui pilotent l'adaptation sans être du contenu visible.

## Cas où la page n'existe pas

Le Controller ne doit jamais redécider ces cas. Il doit recevoir `null` lorsque `PublicListingReadModelBuilder` n'a produit aucun modèle : Search absente, SEO absente ou page supprimée. Une incohérence structurelle doit avoir été levée en amont, pas convertie silencieusement en page partielle.

## Blocage HTTP

`routes/web.php` est vide et aucun resolver, query port, binding Laravel ou stockage de Read Model n'existe. Le Controller demandé est censé recevoir un `PublicListingReadModel`, mais Laravel ne peut pas résoudre cet objet depuis un identifiant d'URL sans dépendance supplémentaire.

Les solutions suivantes sont refusées :

- accéder aux quatre repositories depuis le Controller;
- reconstruire les projections dans le Controller;
- créer un Repository/Catalog de Read Models;
- créer une route dont le paramètre objet n'a aucun binding et provoque une erreur d'exécution;
- utiliser un Service Provider interdit pour fabriquer une donnée fictive;
- créer une route qui répond toujours 404.

## Décisions minimales nécessaires

ContentSeo doit fournir, dans la décision puis la projection :

- la valeur robots HTML finale, déjà décidée;
- un structured data public final, avec vocabulaire et enveloppe JSON-LD explicitement décidés.

L'Application doit ensuite définir un mécanisme autorisé pour fournir le `PublicListingReadModel` à l'adaptateur HTTP. Il peut s'agir d'un query port en lecture seule ou d'une orchestration en amont, mais son ownership et son implémentation doivent être cadrés sans créer une cinquième persistance.

Une fois ces contrats certifiés, le Controller pourra rester strictement limité à `200 + view` ou `404`.
