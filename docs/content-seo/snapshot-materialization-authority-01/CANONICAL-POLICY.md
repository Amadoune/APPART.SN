# Canonical Policy

Règle finale fermée : `canonicalPath = annonces/{listingId}` avec ListingId UUID canonique minuscule.

Une policy ContentSeo dédiée doit encapsuler cette construction ; la chaîne ne doit pas être dispersée dans l’orchestrateur. `CanonicalPolicy::fromPath()` produit ensuite `https://appart.sn/{canonicalPath}` et `CanonicalUrl` valide l’URL.

La valeur est immuable et indépendante de title, description, Geography, PropertyType, Media et SearchRank. Pour RC2 : `annonces/979cd5aa-ced1-48a1-8adf-8b29c843a0c2`.
