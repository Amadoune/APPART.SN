# Phase 5.3C — Moderation Target Read Audit

## Contrat attendu

Pour chaque cible :

`Eligible`, `Ineligible`, `Missing`, `Corrupted`,
`DependencyUnavailable`.

Le reader doit être public, owner-scoped, versionné, read-only et fail-closed.

## Listing

### État réel

`PublicListingQuery` lit un `PublicListingReadModel` par canonical path et
retourne `null` en absence. `ListingRegistry` et le workflow de publication
exposent des composants propriétaires, mais aucun reader public d'éligibilité
par `ListingId`.

### Compatibilité

- la projection publique ne couvre pas les Listings non publiés ou suspendus ;
- `null` ne distingue pas Missing, Corrupted et DependencyUnavailable ;
- le read model public expose plus que la décision minimale ;
- Registry, repository et SQL sont interdits.

### Décision

**Amendement versionné requis :
`A-5.3-LISTING-MODERATION-BOUNDARY-01`.**

## Media

### État réel

`PublicMediaDecisionReader` lit une collection et produit `Found`, `Missing` ou
`Corrupted`. `MediaCollectionOwnershipLookup` résout une collection depuis une
Property. Aucun contrat ne décide l'éligibilité d'un `MediaId` comme cible de
modération.

### Compatibilité

- la granularité collection ne correspond pas à la cible Media ;
- `DependencyUnavailable` et `Ineligible` sont absents ;
- la projection publique ne couvre pas tous les états du lifecycle ;
- les stores lifecycle et ownership ne sont pas des frontières de modération.

### Décision

**Amendement versionné requis :
`A-5.3-MEDIA-MODERATION-BOUNDARY-01`.**

## Account

### État réel

`AccountAvailabilityInspector` est un contrat Application public avec un
catalogue fermé. Ses purposes ne contiennent aucun usage de modération. Son
résultat expose `AccountStatusState`, versions et Closure state propriétaires.

### Compatibilité

- l'owner et le fail-closed sont compatibles en principe ;
- la finalité contractuelle ne couvre pas la modération ;
- le résultat ne correspond pas à la vue minimale Target Read ;
- étendre directement le purpose modifierait F-17.

### Décision

**Amendement versionné requis :
`A-5.3-ACCOUNT-MODERATION-BOUNDARY-01`.**

## ProfessionalProfile

### État réel

`ProfessionalPublicStatusReaderV1` est un contrat public certifié et produit :
`Available`, `Unavailable`, `Missing`, `Corrupted`,
`DependencyUnavailable`.

`ProfessionalPublicProfileStore` lit la visibilité du profil mais constitue un
port de Persistence, non une frontière publique de consommation.

### Compatibilité

Le status reader peut fournir la composante Professional Status, mais il ne
décide pas :

- si le Professional Profile ciblé existe ;
- si ce profil est visible ou éligible ;
- si l'identifiant désigne la bonne autorité 5.2C.

Composer directement avec le store violerait la frontière gelée.

### Décision

**Amendement versionné requis :
`A-5.3-PROFESSIONAL-MODERATION-BOUNDARY-01`.**

Le futur reader pourra composer le status reader certifié, mais devra rester
owner Professional Profile/Status et exposer uniquement la décision minimale.

## Synthèse

| Cible | Reader proche existant | Réutilisable directement | Décision |
|---|---|---:|---|
| Listing | `PublicListingQuery` | Non | Amendement |
| Media | `PublicMediaDecisionReader` | Non | Amendement |
| Account | `AccountAvailabilityInspector` | Non | Amendement |
| ProfessionalProfile | `ProfessionalPublicStatusReaderV1` partiel | Non | Amendement |

Aucune catégorie n'est retirée. Toutes restent désactivées jusqu'à certification
de leur frontière owner.
