# APPART.TEST Public Search Experience 01 — UI-to-Contract Matrix

| Besoin UI | Route cible | Contrat/read model disponible | Source | Statut |
|---|---|---|---|---|
| Saisir une requête opaque | `/api/search/query-resolution` | `PublicSearchQueryResolutionReaderV1` | `SearchQueryResolutionOwnerSource` | PARTIAL |
| Transaction acheter/louer | aucune | aucun champ contractuel structuré | aucune | MISSING |
| Localisation | aucune | aucun champ contractuel structuré | aucune | MISSING |
| Type de bien | aucune | aucun champ contractuel structuré | aucune | MISSING |
| Budget | aucune | aucun champ contractuel | aucune | NOT_SUPPORTED |
| Liste de résultats | aucune | aucun résultat public contenant des annonces | aucune | MISSING |
| Nombre de résultats | aucune | aucun compteur public | aucune | MISSING |
| Pagination | aucune | aucun curseur/page public | aucune | MISSING |
| Carte annonce | aucune surface de collection | `PublicListingReadModel` seulement après chemin exact | projection publique | BLOCKED |
| Fiche annonce | `/{canonicalPath}` | `PublicListingQuery::findByCanonicalPath()` | projection publique PostgreSQL | BLOCKED — zéro projection active |
| État vide | API Query Resolution | statut `empty` | owner source | AVAILABLE |
| État dépendance indisponible | API Query Resolution | statut `dependency_unavailable` | owner source | AVAILABLE, observé HTTP 503 |

La matrice interdit de dériver silencieusement une collection d'annonces depuis un simple statut.
