# SEO Projection — Sprint 3.3

## Statut

`NO GO — conception bloquée par l'absence de décisions sources`.

Ce document décrit le modèle cible sans prétendre qu'il est actuellement reconstructible.

## Modèle cible

Un futur `SeoListingProjection` pourra être un objet `readonly` sans comportement métier contenant uniquement des décisions déjà acquises, par exemple canonical, robots, breadcrumb, structured data et dates. Il ne devra ni choisir une URL, ni décider l'indexabilité, ni construire une hiérarchie éditoriale.

Aucune classe PHP n'est créée dans ce sprint : un constructeur acceptant des champs non démontrables donnerait l'illusion d'un pipeline valide, tandis qu'un builder retournant systématiquement `null` échouerait au critère de réutilisation par les pages publiques.

## Reconstruction attendue après arbitrage

1. charger les trois Aggregates certifiés pour les faits métier;
2. charger ou recevoir les décisions ContentSeo autorisées;
3. vérifier uniquement la cohérence des identités et la présence des décisions;
4. copier les valeurs dans un objet immutable;
5. retourner `null` lorsque la décision SEO indique qu'aucune page ne doit être produite.

La reconstruction ne devra générer aucun horodatage, slug, URL, contenu ou choix robots.

## Dépendances manquantes

- décision de canonical et son historique;
- décision explicite d'indexabilité et robots;
- titre et description publics;
- référentiel public de breadcrumb;
- ressource média publique;
- traitement SEO des Listings expirés.

Ces dépendances appartiennent conceptuellement à ContentSeo et aux référentiels publics, pas aux Aggregates Property, Listing ou MediaCollection.

## Exclusions maintenues

- aucun Repository ou Registry supplémentaire;
- aucune persistance, migration ou SQL;
- aucune modification des quatre repositories PostgreSQL;
- aucun Dispatcher, Outbox ou Unit of Work;
- aucune URL ou donnée structurée par convention implicite;
- aucune règle SEO déplacée dans un Aggregate métier ou dans un builder.

## Condition de reprise

Le sprint peut reprendre dès que le périmètre autorise une source de décisions ContentSeo explicite, ou qu'une décision métier certifiée fournit les champs manquants. À ce moment, le modèle et le builder pourront être implémentés puis soumis aux scénarios canonical stable, breadcrumb stable, Listing expiré et reconstruction déterministe.
