# Owner Eligibility Evidence

L'autorité de lecture est `PostgreSqlPublicMediaBinaryOwnerSourceReader`, appartenant à Media Public Delivery.

Le résultat `Found` n'est produit que si les faits owner-side convergent : média existant et actif, asset existant et `ready`, version exacte, attachment `applied`/`already_applied`, et Listing lié publié avec handoff publié. Les faits Public Projection et Search ne sont jamais lus.

Toute connaissance isolée d'un `mediaId` reste insuffisante : inactive, unattached, unready, privée, obsolète ou inconnue est réduite uniformément en 404.
