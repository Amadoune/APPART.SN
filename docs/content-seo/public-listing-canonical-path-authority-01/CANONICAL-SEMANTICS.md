# Canonical Semantics

`canonicalPath` est l’adresse publique relative, stable et canonique d’un Listing. Il sert d’identité de résolution dans `PublicListingQuery` et de clé de projection, mais ne remplace pas le ListingId interne.

Il est distinct de l’URL absolue. ContentSeo transforme ensuite ce path en canonical URL via `CanonicalPolicy`.
