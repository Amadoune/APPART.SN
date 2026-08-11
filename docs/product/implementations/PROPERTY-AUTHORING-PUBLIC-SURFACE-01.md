# Property Authoring Public Surface 01

## Objectif

La surface Authoring authentifiée permet désormais au propriétaire de préparer et relire les trois faits nécessaires au futur parcours P05 :

- `propertyType` ;
- `city` ;
- `neighborhood`.

## Frontière retenue

Ces valeurs appartiennent à `PropertyAuthoringState` tant que le bien est en préparation. Elles ne construisent pas un Aggregate `Property`, ne déclenchent aucun lifecycle et ne deviennent ni projection ni fait public.

La surface réutilise exclusivement :

- `POST /api/authoring/properties/{propertyId}` pour l'initialisation owner-scoped ;
- `PATCH /api/authoring/properties/{propertyId}` pour une modification optimistically locked ;
- `GET /api/authoring/properties/{propertyId}` pour la relecture owner-scoped ;
- `POST /api/public-authoring/v1/initiate-property|update-property` pour la composition P05 simplifiée.

Toutes ces routes restent protégées par la session IAM certifiée. L'identité propriétaire provient exclusivement de la session.

## Catalogue et validation

Le catalogue `propertyType` reprend les valeurs existantes du domaine utiles à P05 : `apartment`, `house`, `villa`, `land`, `office`, `commercial`. Ville et quartier reprennent les bornes de nom géographique existantes (2 à 120 caractères), sans résolution implicite ni nouvelle décision métier.

## Persistence

La migration additive `094_property_authoring_public_surface.sql` ajoute trois colonnes nullables à la source Authoring 056. Le rollback 094 retire exclusivement ces colonnes et leurs contraintes.

Les lignes historiques restent lisibles avec les trois valeurs `null`. Aucun backfill et aucune valeur par défaut ne sont introduits.
