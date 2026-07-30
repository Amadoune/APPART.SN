# Search Projection Analysis — Sprint 3.2

## Décision

La première fiche de recherche est un instantané immutable construit directement depuis `Property`, `MediaCollection` et `Listing`. Elle ne possède ni identité autonome, ni version métier, ni événements, ni Registry. Elle n'est jamais une source de vérité et peut être supprimée puis reconstruite sans perte.

Le module `SearchDiscovery` existant décrit un modèle futur plus large (`SearchIndex`, politiques de visibilité/fraîcheur, facettes et port de persistance). Il n'est ni étendu ni persisté dans ce sprint : la fondation demandée est une fiche passive, sans règle ni nouvelle infrastructure.

## Audit des sources

### Property

Informations exposables : `PropertyId`, type, surface éventuelle, nombre de pièces et `GeographicPlaceId` éventuel.

Décisions métier conservées dans l'Aggregate : validité des caractéristiques selon le type, statut actif/archivé, mutation et chronologie.

Informations non projetées : référence interne, nombre de salles de bain, année de construction et ligne d'adresse exacte. Elles ne sont pas nécessaires à la première fiche demandée; la ligne exacte est en outre plus précise que le besoin de recherche publique.

Le Domain n'expose pas de libellé de ville ou de quartier. Transformer arbitrairement `place:dakar:plateau` en deux libellés créerait une convention spéculative. La projection conserve donc uniquement l'identité géographique; une future composition avec Geography pourra fournir des libellés autorisés.

### MediaCollection

Informations exposables : `MediaCollectionId` et `MediaId` du média principal actif.

Décisions métier conservées dans l'Aggregate : propriété de la collection, statut actif/terminal, unicités, ordre et choix du principal.

Le Domain n'expose ni URL, ni variante publique. Aucune URL n'est inventée. L'absence de principal signifie qu'aucune fiche publique complète ne peut être construite, conformément au pipeline 3.1.

### Listing

Informations exposables : `ListingId`, `PropertyId`, statut courant, date de la révision ayant produit l'état `Published` et expiration.

Décisions métier conservées dans l'Aggregate : graphe de transitions, triggers/origins, éligibilité Property/Media, révision, expiration et publication.

La date de publication est une donnée dérivée sans décision : c'est `occurredAt` de la dernière révision dont le statut résultant est `Published`. La projection ne publie jamais le Listing; elle ne fait que constater son état.

## Champs retenus

| Champ | Source | Justification |
|---|---|---|
| `listingId` | Listing | Identité stable de la fiche et de l'annonce. |
| `propertyId` | Property/Listing | Rattachement à la source catalogue et contrôle de cohérence. |
| `mediaCollectionId` | MediaCollection | Traçabilité de la galerie source. |
| `listingStatus` | Listing | État officiel constaté; la première projection publique n'existe que pour `published`. |
| `geographicPlaceId` | Property.Address | Clé géographique disponible sans inventer ville ou quartier. Nullable car l'adresse est nullable dans le Domain. |
| `propertyType` | Property | Filtre de recherche explicitement porté par le catalogue. |
| `surfaceSquareMeters` | Property | Information de comparaison; nullable comme la source. |
| `roomCount` | Property | Information de comparaison demandée et non dérivée. |
| `primaryMediaId` | MediaCollection | Visuel principal désigné par l'Aggregate, sans fabriquer d'URL. |
| `publishedAt` | ListingRevision | Date dérivée de la transition effective vers `Published`. |
| `expiresAt` | Listing | Borne publique décidée lors de la publication. |

## Informations dérivées et exclusions

Le builder réalise uniquement des copies, une recherche déterministe de révision et des contrôles de cohérence des sources. Il retourne `null` si la fiche publique n'est pas reconstructible : Listing non publié, Property archivée, principal absent, expiration/révision de publication absente ou identités Property incohérentes.

Ces contrôles ne décident aucun état métier. Ils observent les décisions déjà acquises par les Aggregates et évitent de fabriquer une lecture contradictoire. Prix, titre, description, coordonnées, score, rang, facettes libres, URL, ville et quartier sont exclus faute de source actuelle justifiée.
