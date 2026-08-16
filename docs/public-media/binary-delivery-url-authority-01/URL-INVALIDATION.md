# URL invalidation

Une nouvelle asset revision produit un nouveau locator versionné. L'ancien locator n'est plus servi dès qu'il ne correspond plus à la version ready/attachment courante : HTTP 404.

Pas de redirect, alias ni conservation éternelle. Une migration backend transparente ne change pas le locator si la révision et le contenu restent identiques.
