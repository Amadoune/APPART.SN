# ContentSeo adapter evidence

`PublicGeographySeoSourceV2` est distinct de V1 et contient seulement ListingId, locality et items `(placeId,type,label)`. `ListingSeoDecisionPolicy` accepte les deux versions.

Pour V2, la policy conserve indexability, locality/structured data, decisionAt, snapshot identity et canonical Listing. Elle ne crée aucun `BreadcrumbItem(label,url)` Geography; le seul item URL-bearing restant est le canonical Listing existant.
