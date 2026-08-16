# Canonical Integration

Une policy ContentSeo dédiée reçoit un ListingId Published valide et retourne `annonces/{uuid-minuscule}`. Elle ne lit aucune autre donnée.

Pour RC2 :

- path : `annonces/979cd5aa-ced1-48a1-8adf-8b29c843a0c2` ;
- URL via `CanonicalPolicy` : `https://appart.sn/annonces/979cd5aa-ced1-48a1-8adf-8b29c843a0c2`.

Le résultat reste identique malgré toute évolution title, description, Geography, PropertyType, Media ou SearchRank.
