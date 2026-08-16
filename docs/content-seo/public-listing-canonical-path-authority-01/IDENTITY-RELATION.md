# Identity Relation

Fonction normative :

`canonicalPath(ListingId L) = "annonces/" + canonicalLowercaseUuid(L)`.

La relation est totale uniquement pour un ListingId valide et un Listing Published. Même identité et mêmes faits d’éligibilité produisent toujours le même path.
