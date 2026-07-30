# SEO Projection Analysis — Sprint 3.3

## Verdict de l'analyse

La projection demandée ne peut pas être produite exclusivement depuis `Property`, `Listing` et `MediaCollection` sans inventer des décisions SEO. Le Sprint 3.3 est donc bloqué au stade de conception et doit être classé `NO GO` tant qu'une source de décisions SEO n'est pas autorisée.

Créer malgré cela `SeoListingProjection` et un builder qui retourne une page pour un Listing publié violerait simultanément « aucune règle métier », « uniquement représenter les décisions déjà prises » et « ne rien inventer ».

## Informations réellement disponibles

### Property

Disponible : `PropertyId`, type, surface éventuelle, pièces, statut actif/archivé, `GeographicPlaceId` éventuel et ligne d'adresse.

Absent : nom public de ville, nom public de quartier, slugs et aliases géographiques. La ligne d'adresse ne doit pas être transformée en slug ou exposée par défaut.

### MediaCollection

Disponible : `MediaCollectionId`, rattachement Property et `MediaId` du principal actif éventuel.

Absent : URL publique, variante d'image, texte alternatif public et décision d'usage SEO.

### Listing

Disponible : `ListingId`, rattachement Property, statut officiel, révisions, date de la dernière transition vers `Published` et expiration éventuelle.

Absent : titre, description, prix, intention, canonical path, slug public, URL historique et décision de qualité SEO.

## Audit des sorties demandées

| Sortie | Démontrable depuis les trois Aggregates | Conclusion |
|---|---:|---|
| URL | Non | Aucun pattern d'URL n'est possédé par les sources. |
| Canonical | Non | La canonical est une décision ContentSeo page par page, avec historique et unicité. |
| Indexabilité | Non | `Published` rend seulement éligible; la qualité minimale et la décision SEO manquent. |
| Robots | Non pour une page active | `index,follow` dépend de l'indexabilité. Pour les états non publiés, aucune décision de maintien de page n'est disponible. |
| Breadcrumb | Non | Les libellés, slugs, destinations publiques, intention, ville et quartier manquent. |
| Structured data | Non | Nom, description, URL, localité publique et autres faits visibles obligatoires manquent. |
| Date de publication | Oui | Dernière révision dont l'état résultant est `Published`. |
| Date d'expiration | Oui pour un Listing publié | `Listing::expirationDate()`. |
| Identités et caractéristiques Property | Oui | Utiles comme sources, insuffisantes pour constituer une page SEO. |
| Média principal | Oui comme identité | Insuffisant sans URL/variante publique et autorisation SEO. |

## Preuves dans le Domain existant

- `SEO-POLICY.md` stipule qu'une annonce publiée est « éligible si qualité suffisante » et que la canonical est choisie page par page.
- `DOMAIN-MAPPING.md` attribue URL de référence, décision d'indexation, breadcrumb et sitemap à ContentSeo.
- `ContentSeo\Domain\Model\ListingSeoSource` exige déjà `headline`, `description` et `canonicalPath`, absents de Listing.
- `SeoGenerationPolicy` exige aussi une source Search publique et une ville qualifiée; elle ne déduit pas ces faits des trois Aggregates.
- `CanonicalHistoryPolicy` et `SeoProjectionRegistry` montrent que stabilité, historique et unicité canonical ne sont pas des propriétés calculables depuis `ListingId`.

## Pourquoi les solutions faciles sont refusées

- `https://appart.sn/annonces/{listingId}` : nouveau schéma d'URL non décidé et ignorant le patrimoine historique.
- Slug issu du type ou de l'adresse : collision, instabilité et exposition potentielle d'une donnée précise.
- `index,follow` pour tout Listing publié : confusion entre publication métier, qualité SEO et indexabilité.
- Breadcrumb `Accueil > Annonces > {id}` : hiérarchie et destinations publiques non décidées.
- Structured data limité aux identités techniques : non conforme aux faits visibles requis par le Domain ContentSeo.
- Builder retournant toujours `null` : techniquement déterministe, mais pas directement réutilisable par une future page et donc non conforme au critère de GO.

## Décision minimale nécessaire pour débloquer

Le prochain cadrage doit autoriser le builder à lire une décision ContentSeo déjà acquise, au minimum :

- canonical path courant et historique stable;
- titre et description publics validés;
- décision d'indexation/robots;
- libellés et chemins breadcrumb issus de référentiels publics;
- URL/variante du média principal autorisée;
- décision de maintien ou disparition pour une annonce expirée.

Cette source doit rester distincte des trois Aggregates métier. Ajouter ces responsabilités à Property, Listing ou MediaCollection déplacerait illégitimement les règles SEO.
