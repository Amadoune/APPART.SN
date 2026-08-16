# Uniqueness Model

L’unicité globale découle de l’unicité du ListingId UUID et du préfixe fermé `annonces/`.

Aucun registre canonical ni séquence n’est requis pour calculer le path. Le store Public Projection conserve néanmoins sa protection de collision : deux Listings différents ne peuvent pas posséder la même clé canonical.
