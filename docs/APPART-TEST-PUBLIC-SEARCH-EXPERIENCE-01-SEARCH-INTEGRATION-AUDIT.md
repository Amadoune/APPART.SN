# APPART.TEST Public Search Experience 01 — Search Integration Audit

## Surfaces certifiées observées

| Surface | Capacité réelle | Limite pour le parcours UI |
|---|---|---|
| `GET /api/search/query-resolution` | Résout une chaîne opaque et renvoie un statut fermé | Aucun résultat, filtre, compteur ou lien d'annonce |
| `PublicSearchQueryResolutionReaderV1` | Reader public status-only | Payload limité au statut |
| `SearchQueryReaderV1` | Contrat Search Experience `read(query, observedAt)` | Résultat limité à `Found`, `Empty`, `Corrupted`, `DependencyUnavailable` |
| `SearchQuery` | Une valeur canonique opaque | Aucun champ transaction/localisation/type/budget structuré |
| `PublicListingQuery` | Résolution d'un chemin canonique exact | Aucune énumération, recherche, pagination ou facette |
| `PublicListingController` | Affiche une projection connue par son chemin canonique | Nécessite une projection publique réelle existante |
| `PublicListingReadModel` | Détail complet d'une annonce publique | Disponible uniquement après résolution exacte |

## Bindings et runtime

`PublicSearchQueryResolutionReaderServiceProvider` lie le Reader public à la source owner-scoped certifiée. `PublicProjectionRuntimeServiceProvider` lie `PublicListingQuery` au store PostgreSQL de projection publique. Aucun binding concret de `SearchExperiencePublicRead\SearchQueryReaderV1` fournissant une collection d'annonces n'existe.

## État PostgreSQL local observé

- PostgreSQL 18.4 : connecté ;
- générations publiques actives : `0` ;
- projections publiques courantes dans une génération active : `0` ;
- décisions publiques Search : `0` ;
- index courant Query Resolution : `1` entrée technique ;
- chemins canoniques publics disponibles : aucun.

Probe HTTP réel `acheter dakar` : HTTP 503. Aucun chemin canonique ne permet de démontrer une fiche annonce HTTP 200.

## Conclusion

Les capacités existantes permettent de qualifier un statut de résolution et de lire une fiche dont le chemin est déjà connu. Elles ne fournissent pas la collection de résultats nécessaire au parcours Recherche → Résultats → Fiche.
