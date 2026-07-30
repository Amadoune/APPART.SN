# Public Listing Read Model Analysis — Sprint 3.4

## Verdict de composabilité

`GO`. Les deux projections certifiées suffisent à produire une fiche publique utile sans inventer de donnée. Search apporte l'identité, les caractéristiques du bien, le média principal et les dates de publication. SEO apporte le contenu éditorial, la ressource média publique et les métadonnées de page.

Le modèle reste volontairement sans prix, adresse publique précise, téléphone, ville ou quartier nommés : ces informations ne sont pas disponibles dans les deux sources.

## Champs disponibles

### SearchListingProjection

- ListingId, PropertyId, MediaCollectionId;
- statut Listing;
- GeographicPlaceId éventuel;
- type Property, surface éventuelle, nombre de pièces;
- MediaId principal;
- publication et expiration.

### SeoListingProjection

- ListingId;
- headline et description éventuels;
- canonical et historique;
- indexabilité et robots;
- breadcrumb et structured data;
- URL du média public éventuelle;
- publication et expiration éventuelles;
- date de décision et traitement expiré.

## Doublons et cohérence

Les seuls doublons vérifiables sont `listingId`, `publishedAt` et `expiresAt`. Ils doivent être identiques lorsque les dates SEO sont présentes. Search reste l'autorité de copie des dates dans le Read Model car elles y sont obligatoires.

Le statut Search doit être `published`. Les couples structuraux SEO acceptés sont `indexable/index_follow` et `not_indexable/noindex_follow`. Une projection SEO portant `expiredListingTreatment=remove` est impossible : le builder SEO aurait dû retourner `null`; elle est donc refusée.

## Limites de rapprochement

- `primaryMediaId` et `publicMediaUrl` sont copiés, mais aucune clé commune ne permet de prouver leur correspondance;
- `geographicPlaceId` et les niveaux breadcrumb sont copiés sans traduction ni rapprochement;
- la fraîcheur relative des projections n'est pas calculable au-delà de leurs dates communes;
- headline, description, structured data et URL média peuvent être absents sur une page noindex conservée.

Ces limites ne rendent pas la fiche inutilisable : une fiche indexable est complète par construction SEO, tandis qu'une fiche noindex peut représenter explicitement une page dégradée ou temporaire.

## Informations interdites à inventer

Prix, intention commerciale, adresse exacte, ville, quartier, coordonnées, annonceur, nouvelle URL, slug, contenu de remplacement, URL média dérivée, score ou données structurées supplémentaires.

## Règles d'existence

- Search absente : `null`;
- SEO absente : `null`, sans tenter d'en déterminer la cause;
- Search + SEO indexable : Read Model indexable;
- Search + SEO conservée en noindex : Read Model noindex;
- identités, dates ou états structurels incohérents : `InconsistentPublicListingProjection`;
- aucune valeur partielle n'est fabriquée pour réparer une incohérence.
